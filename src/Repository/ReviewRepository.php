<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\Info;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\ReviewFav;
use Doctrine\ORM\Query;
use Gedmo\SoftDeleteable\Query\TreeWalker\SoftDeleteableWalker;
use App\Service\UserClient;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    const ADD_NOTIFY_MSG_URL = '/msg/notify/add';

    const USER_DEL_NOTIFY_MSG = '/msg/notify/del';

    private $userClient;
    /**
     * @var InfoRepository $infoRepo
     */
    private $infoRepo;
    /**
     * @var ReviewFavRepository $infoRepo
     */
    private $reviewFavRepo;
    public function __construct(RegistryInterface $registry, UserClient $userClient)
    {
        parent::__construct($registry, Review::class);
        $this->infoRepo = $this->_em->getRepository(Info::class);
        $this->reviewFavRepo = $this->_em->getRepository(ReviewFav::class);
        $this->userClient = $userClient;
    }

    public function reply($infoId, $userId, $content, $replyTo = null, $pid = 0)
    {
        // 查看info
        $info = $this->infoRepo->findOneBy(['id' => $infoId]);
        if (empty($info)) {
            throw new \Exception('信息不存在');
        }

        $review = (new Review())
            ->setUserId($userId)
            ->setInfoId($infoId)
            ->setContent($content)
            ->setPid($pid)
            ->setFavCount(0)
            ->setReplyCount(0);
        
        if (!empty($replyTo)) {
            $review = $review->setReplyTo($replyTo);
        }
        
        $this->_em->persist($review);
        $this->_em->flush();

        if (!empty($replyTo)) {
            $this->userClient->call(self::ADD_NOTIFY_MSG_URL, [
                'user_id' => $replyTo,
                'notifier_id' => $userId,
                'info_id' => $infoId,
                'review_id' => $review->getId(),
            ]);
        } else {
            // 回复一级评论或信息流
            if (!empty($pid)) {
                $r = $this->findOneBy(['id' => $pid]);
                $replyTo = $r->getUserId();
            } else {
                $replyTo = $info->getUserId();
            }
            $this->userClient->call(self::ADD_NOTIFY_MSG_URL, [
                'user_id' => $replyTo,
                'notifier_id' => $userId,
                'info_id' => $infoId,
                'review_id' => $review->getId(),
            ]);
        }

        return $review;
    }

    public function reviewDetail($reviewId)
    {
        return (array) $this->find($reviewId);
    }

    public function del($reviewId)
    {
        $review = $this->find($reviewId);
        if (empty($review) || $review->isDeleted()) {
            return;
        }

        $this->_em->transactional(function ($em) use ($review) {
            $r = $em->createQueryBuilder()
                ->delete('App:Review', 'r')
                ->where('r.id = :review_id OR r.pid = :review_id')
                ->setParameters([
                    'review_id' => $review->getId(),
                ]);
            $r->getQuery()
                ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SoftDeleteableWalker::class)
                ->getResult();
            
            // 找到所有子评论并且有reply_to的id
            $children = $this->_em->createQueryBuilder()
                    ->select('r')
                    ->from('App:Review', 'r')
                    ->where('r.pid = :pid AND r.reply_to is not null')
                    ->setParameters(['pid' => $review->getId()])
                    ;
            $c = $children->getQuery()->getResult(Query::HYDRATE_ARRAY);
            foreach ($c as $i) {
                $this->userClient->call(self::USER_DEL_NOTIFY_MSG, ['review_id' => $i['id']]);
            }

            $this->userClient->call(self::USER_DEL_NOTIFY_MSG, ['review_id' => $review->getId()]);
            $this->_em->flush();
        });

        return;
    }

    public function list($infoId, $relativedId = 0, $page = 1, $pageSize = 10)
    {
        // dd(date('Y-m-d\TH:i:sP'));
        // dd((new \DateTime("2019-05-31 15:00:00", new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:sP'));
        $query = $this->_em->createQueryBuilder();
        $query->select('r.id, r.user_id, r.content, r.reply_to, r.pid, r.fav_count, r.createdAt as created_at')
            ->from('App:Review', 'r')
            ->where('r.info_id = :info_id AND r.pid = 0 AND r.deletedAt is null')
            ->orderBy('r.fav_count', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->setParameters(['info_id' => $infoId])
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ;
        $p = new Paginator($query);
        $p->setUseOutputWalkers(false);

        $list = [];
        if (0 === count($p)) {
            return $list;
        }

        foreach ($p as $i) {
            $reviewFav = $this->reviewFavRepo->findOneBy([
                'review_id' => $i['id'],
                'user_id' => $relativedId,
                'deletedAt' => null,
            ]);
            $i['fav_status'] = empty($reviewFav) ? 'no' : 'yes';
            $childrenCount = $this->_em->createQueryBuilder()
                ->select('count(r.id)')
                ->from('App:Review', 'r')
                ->where('r.pid = :pid AND r.deletedAt is null')
                ->setParameters(['pid' => $i['id']]);
            $i['children_count'] = (int) $childrenCount->getQuery()->getSingleScalarResult();
            $i['created_at'] = $i['created_at']->format(\DateTime::W3C);
            $list[] = $i;
        }

        return $list;
    }

    public function fav($reviewId, $userId)
    {
        $review = $this->find($reviewId);
        if (empty($review)) {
            throw new \Exception('回复不存在');
        }
        $query = $this->_em->createQueryBuilder()
            ->select('count(rf.id)')
            ->from('App:ReviewFav', 'rf')
            ->where('rf.review_id = :review_id AND rf.user_id = :user_id AND rf.deletedAt is null')
            ->setParameters(['review_id' => $reviewId, 'user_id' => $userId]);

        if (0 < $query->getQuery()->getSingleScalarResult()) {
            return;
        }

        $reviewFav = (new ReviewFav())
                ->setReviewId($reviewId)
                ->setUserId($userId);
        $this->_em->transactional(function ($em) use ($reviewFav, $reviewId) {
            $em->persist($reviewFav);
            $em->flush();
            $this->updateFavCount($reviewId);
            $em->flush();
        });
        
        return;
    }


    public function unFav($reviewId, $userId)
    {
        $review = $this->find($reviewId);
        if (empty($review)) {
            throw new \Exception('回复不存在');
        }
        $reviewFav = $this->reviewFavRepo->findOneBy([
            'review_id' => $reviewId,
            'user_id' => $userId,
            'deletedAt' => null
        ]);
        if (empty($reviewFav)) {
            return;
        }

        $this->_em->transactional(function ($em) use ($reviewId, $reviewFav) {
            $em->remove($reviewFav);
            $em->flush();
            $this->updateFavCount($reviewId);
            $em->flush();
        });
        
        return;
    }

    public function updateFavCount($reviewId)
    {
        $review = $this->find($reviewId);

        $query = $this->_em->createQueryBuilder()
            ->select('count(rf.id)')
            ->from('App:ReviewFav', 'rf')
            ->where('rf.review_id = :review_id AND rf.deletedAt is null')
            ->setParameters(['review_id' => $reviewId]);
        $review->setFavCount($query->getQuery()->getSingleScalarResult());
        $this->_em->merge($review);
    }

    public function listByPid($pid, $relativedId = 0, $page = 1, $pageSize = 10)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('r.id, r.user_id, r.content, r.reply_to, r.pid, r.fav_count, r.createdAt as created_at')
            ->from('App:Review', 'r')
            ->where('r.pid = :pid AND r.deletedAt is null')
            ->orderBy('r.fav_count', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->setParameters(['pid' => $pid])
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ;
        $p = new Paginator($query);
        $p->setUseOutputWalkers(false);

        $list = [];
        if (0 === count($p)) {
            return $list;
        }

        foreach ($p as $i) {
            $reviewFav = $this->reviewFavRepo->findOneBy([
                'review_id' => $i['id'],
                'user_id' => $relativedId,
                'deletedAt' => null,
            ]);
            $i['created_at'] = $i['created_at']->format('Y-m-d\TH:i:sP');
            $i['fav_status'] = empty($reviewFav) ? 'no' : 'yes';
            $list[] = $i;
        }

        return $list;
    }
    // /**
    //  * @return Review[] Returns an array of Review objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
