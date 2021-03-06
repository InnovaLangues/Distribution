<?php

namespace Innova\PathBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\TeamBundle\Manager\TeamManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Event\Log\LogStepUnlockDoneEvent;
use Innova\PathBundle\Event\Log\LogStepUnlockEvent;
use Innova\PathBundle\Manager\UserProgressionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class StepConditionController.
 *
 * @Route(
 *      "/stepconditions",
 *      name    = "innova_path_stepcondition",
 *      service = "innova_path.controller.step_condition"
 * )
 */
class StepConditionController extends Controller
{
    /**
     * Object manager.
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;
    private $groupManager;
    private $evaluationRepo;
    private $teamManager;
    private $eventDispatcher;
    /**
     * Security Token.
     *
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $securityToken;
    /**
     * User Progression manager.
     *
     * @var \Innova\PathBundle\Manager\UserProgressionManager
     */
    protected $userProgressionManager;

    /**
     * Constructor.
     *
     * @param ObjectManager            $objectManager
     * @param GroupManager             $groupManager
     * @param TokenStorageInterface    $securityToken
     * @param TeamManager              $teamManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserProgressionManager   $userProgressionManager
     */
    public function __construct(
        ObjectManager $objectManager,
        GroupManager $groupManager,
        TokenStorageInterface $securityToken,
        TeamManager $teamManager,
        EventDispatcherInterface $eventDispatcher,
        UserProgressionManager $userProgressionManager
    ) {
        $this->groupManager = $groupManager;
        $this->om = $objectManager;
        $this->securityToken = $securityToken;
        $this->teamManager = $teamManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->userProgressionManager = $userProgressionManager;
    }
    /**
     * Get user group for criterion.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/usergroup",
     *     name         = "innova_path_criteria_usergroup",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getUserGroups()
    {
        $data = [];

        $usergroup = $this->groupManager->getAll();
        if ($usergroup !== null) {
            //data needs to be explicitly set because Group does not extends Serializable
            foreach ($usergroup as $ug) {
                $data[$ug->getId()] = $ug->getName();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Get list of groups a user belongs to.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/groupsforuser",
     *     name         = "innova_path_criteria_groupsforuser",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getGroupsForUser()
    {
        // Retrieve the current User
        $user = $this->securityToken->getToken()->getUser();
        // Retrieve Groups of the User
        if ($user instanceof User) {
            $groups = $user->getGroups();
        } else {
            $groups = [];
        }

        // data needs to be explicitly set because Group does not extends Serializable
        $data = [];
        foreach ($groups as $group) {
            $data[$group->getId()] = $group->getName();
        }

        return new JsonResponse($data);
    }

    /**
     * Get evaluation data for an activity.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/activityeval/{activityId}",
     *     name         = "innova_path_activity_eval",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getActivityEvaluation($activityId)
    {
        $data = [
            'status' => 'NA',
            'attempts' => 0,
        ];
        //retrieve activity
        $this->activityRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity');
        $activity = $this->activityRepo->findOneBy(['id' => $activityId]);
        if ($activity !== null) {
            //retrieve evaluation data for this activity
            $this->evaluationRepo = $this->om->getRepository('ClarolineCoreBundle:Activity\Evaluation');
            $evaluation = $this->evaluationRepo->findOneBy(['activityParameters' => $activity->getParameters()]);
            //return relevant data
            if ($evaluation !== null) {
                $data = [
                    'status' => $evaluation->getStatus(),
                    'attempts' => $evaluation->getAttemptsCount(),
                ];
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Get list of Evaluation statuses to display in select
     * (data from \CoreBundle\Entity\Activity\AbstractEvaluation.php).
     *
     * @Route(
     *     "/activitystatuses",
     *     name         = "innova_path_criteria_activitystatuses",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getEvaluationStatuses()
    {
        $r = new \ReflectionClass('Claroline\CoreBundle\Entity\Activity\AbstractEvaluation');
        //Get class constants
        $const = $r->getConstants();
        $statuses = [];
        foreach ($const as $k => $v) {
            //Only get constants beginning with STATUS
            if (strpos($k, 'STATUS') !== false) {
                $statuses[] = $v;
            }
        }

        return new JsonResponse($statuses);
    }
    /**
     * Get activities of steps of a path.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/activitylist/{path}",
     *     name         = "innova_path_activities",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getActivityList(Path $path)
    {
        $activitylist = [];
        $steps = $this->om->getRepository('InnovaPathBundle:Path')->findById($path);

        foreach ($steps as $step) {
            $activitylist[$step->getId()] = self::getActivityEvaluation($step->getActivity());
        }

        return new JsonResponse($activitylist);
    }

    /**
     * Get evaluation for all steps of a path.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/allevaluations/{path}",
     *     name         = "innova_path_evaluation",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getAllEvaluationsByUserByPath($path)
    {
        $user = $this->securityToken->getToken()->getUser();
        $results = $this->om->getRepository('InnovaPathBundle:StepCondition')->findAllEvaluationsByUserAndByPath((int) $path, $user->getId());

        $jsonresults = [];
        foreach ($results as $r) {
            $jsonresults[] = [
                'eval' => [
                    'id' => $r->getId(),
                    'attempts' => $r->getAttemptsCount(),
                    'status' => $r->getStatus(),
                    'score' => $r->getScore(),
                    'numscore' => $r->getNumScore(),
                    'scooremin' => $r->getScoreMin(),
                    'scoremax' => $r->getScoreMax(),
                    'type' => $r->getType(),
                ],
                'evaltype' => $r->getActivityParameters()->getEvaluationType(),
                'idactivity' => $r->getActivityParameters()->getActivity()->getId(),
                'activitytitle' => $r->getActivityParameters()->getActivity()->getTitle(),
            ];
        }

        return new JsonResponse($jsonresults);
    }

    /**
     * Get list of teams for current WS.
     *
     * @param $id path_id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/teamsforws/{id}",
     *     name         = "innova_path_criteria_teamsforws",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getTeamsForWs($id)
    {
        //retrieve current workspace
        $workspace = $this->om->getRepository("InnovaPathBundle:Path\Path")->findOneById($id)->getWorkspace();

        $data = [];
        //retrieve list of groups object for this user
        $teamsforws = $this->teamManager->getTeamsByWorkspace($workspace);
        if ($teamsforws !== null) {
            //data needs to be explicitly set because Team does not extends Serializable
            foreach ($teamsforws as $tw) {
                $data[$tw->getId()] = $tw->getName();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Get list of teams a user belongs to.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/teamsforuser",
     *     name         = "innova_path_criteria_teamsforuser",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getTeamsForUser()
    {
        //retrieve current user
        $user = $this->securityToken->getToken()->getUser();
        $data = [];
        if ($user instanceof User) {
            //retrieve list of team object for this user
            $teamforuser = $this->teamManager->getTeamsByUser($user, 'name', 'ASC', true);
            if ($teamforuser !== null) {
                //data needs to be explicitly set because Team does not extends Serializable
                foreach ($teamforuser as $tu) {
                    $data[$tu->getId()] = $tu->getName();
                }
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @param Step $step
     * @param Step $nextstep
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route(
     *     "/stepunlock/{step}",
     *     name         = "innova_path_step_callforunlock",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function callForUnlock(Step $step)
    {
        //array of user id to send the notification to = users who will receive the call : the path creator
        $creator = $step->getPath()->getCreator()->getId();
        $userIds = [$creator];
        //create an event, and pass parameters
        $event = new LogStepUnlockEvent($step, $userIds);
        //send the event to the event dispatcher
        $this->eventDispatcher->dispatch('log', $event);

        //update lockedcall value : set to true = called
        $user = $this->securityToken->getToken()->getUser();
        $progression = $this->userProgressionManager
            ->updateLockedState($user, $step, true, null, null, '');
        //return response
        return new JsonResponse($progression);
    }

    /**
     * Ajax call for unlocking step.
     *
     * @Route(
     *     "unlockstep/{step}/user/{user}",
     *     name="innova_path_unlock_step",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function unlockStep(Step $step, User $user)
    {
        $userIds = [$user->getId()];
        //create an event, and pass parameters
        $event = new LogStepUnlockDoneEvent($step, $userIds);
        //send the event to the event dispatcher
        $this->eventDispatcher->dispatch('log', $event);
        //update lockedcall value : set to true = called
        $progression = $this->userProgressionManager
            ->updateLockedState($user, $step, false, false, true, 'unseen');
        //return response
        return new JsonResponse($progression);
    }
}
