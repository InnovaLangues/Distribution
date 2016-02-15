<?php

namespace UJM\ExoBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ExerciseQuestionRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ExerciseQuestionRepository extends EntityRepository
{
    /**
     * Exercises use the question.
     *
     *
     * @param int $questionId id Question
     *
     * Return array[ExerciseQuestion]
     */
    public function getExercises($questionId)
    {
        $qb = $this->createQueryBuilder('eq');
        $qb->join('eq.question', 'q')
            ->where($qb->expr()->in('q.id', $questionId));

        return $qb->getQuery()->getResult();
    }

    /**
     * Number of question for an exercise.
     *
     *
     * @param int $exoId if Exercise
     *
     * Return aintger
     */
    public function getCountQuestion($exoId)
    {
        $query = $this->_em->createQuery(
               'SELECT count(eq.question) as nbq
                FROM UJM\ExoBundle\Entity\ExerciseQuestion eq
                WHERE eq.exercise = ?1'
        );
        $query->setParameter(1, $exoId);

        return $query->getSingleResult();
    }
}
