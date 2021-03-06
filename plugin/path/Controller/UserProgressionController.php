<?php

namespace Innova\PathBundle\Controller;

use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\UserProgressionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserProgressionController.
 *
 * @Route(
 *      "/progression",
 *      name    = "innova_path_progression",
 *      service = "innova_path.controller.user_progression"
 * )
 */
class UserProgressionController
{
    /**
     * User Progression manager.
     *
     * @var \Innova\PathBundle\Manager\UserProgressionManager
     */
    protected $userProgressionManager;

    /**
     * Class constructor.
     *
     * @param \Innova\PathBundle\Manager\UserProgressionManager $userProgressionManager
     */
    public function __construct(UserProgressionManager $userProgressionManager)
    {
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * Log a new action from User (mark the the  step as to do).
     *
     * @param \Innova\PathBundle\Entity\Step $step
     * @param string                         $status
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/create/{id}/{authorized}/{status}",
     *     name         = "innova_path_progression_create",
     *     requirements = {"id" = "\d+"},
     *     options      = { "expose" = true }
     * )
     * @Method("POST")
     */
    public function createAction(Step $step, $status = null, $authorized = 0)
    {
        $progression = $this->userProgressionManager->create($step, null, $status, $authorized);

        return new JsonResponse(['progression' => $progression]);
    }

    /**
     * Update progression of a User.
     *
     * @param \Innova\PathBundle\Entity\Step $step
     * @param string                         $status
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/step/{id}/{status}/{authorized}",
     *     name         = "innova_path_progression_update",
     *     requirements = {"id" = "\d+"},
     *     options      = { "expose" = true }
     * )
     * @Method("PUT")
     */
    public function updateAction(Step $step, $status, $authorized)
    {
        $progression = $this->userProgressionManager->update($step, null, $status, $authorized);

        return new JsonResponse(['progression' => $progression]);
    }

    /**
     * Set lock for progression of a User.
     *
     * @param \Innova\PathBundle\Entity\Step $step
     * @param bool                           $lock
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/steplock/{id}/{locked}",
     *     name         = "innova_path_progression_setlock",
     *     requirements = {"id" = "\d+"},
     *     options      = { "expose" = true }
     * )
     * @Method("PUT")
     */
    public function setLockAction(Step $step, $locked)
    {
        $progression = $this->userProgressionManager->setLock($step, $locked);

        return new JsonResponse($progression);
    }
}
