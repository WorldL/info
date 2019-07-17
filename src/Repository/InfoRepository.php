<?php

namespace App\Repository;

use App\Entity\Info;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\InfoFav;
use App\Entity\InfoCol;
use App\Entity\InfoImg;
use App\Entity\InfoImgTag;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\Query;
use Gedmo\SoftDeleteable\Query\TreeWalker\SoftDeleteableWalker;
use App\Service\UserClient;
use App\Entity\Review;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Info|null find($id, $lockMode = null, $lockVersion = null)
 * @method Info|null findOneBy(array $criteria, array $orderBy = null)
 * @method Info[]    findAll()
 * @method Info[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoRepository extends ServiceEntityRepository
{
    const USER_VIEW_URL = '/user/view';
    const USER_FOLLOW_STATUS_URL = '/user/follow-status';
    const USER_RELATIVE_FOLLOW_STATUS_URL = '/user/relative-follow-status';
    
    const USER_DEL_NOTIFY_MSG = '/msg/notify/del';

    /**
     * @var InfoFavRepository $infoFavRepo
     */
    private $infoFavRepo;
    /**
     * @var InfoColRepository $infoColRepo
     */
    private $infoColRepo;
    /**
     * @var InfoImgRepository $infoImgRepo
     */
    private $infoImgRepo;
    /**
     * @var InfoImgTagRepository $infoImgTagRepo
     */
    private $infoImgTagRepo;

    /**
     * @var \JMS\Serializer\Serializer $serializer
     */
    private $serializer;

    private $userClient;

    public function __construct(RegistryInterface $registry, SerializerInterface $serializer, UserClient $userClient)
    {
        parent::__construct($registry, Info::class);
        $this->infoFavRepo = $this->_em->getRepository(InfoFav::class);
        $this->infoColRepo = $this->_em->getRepository(InfoCol::class);
        $this->infoImgRepo = $this->_em->getRepository(InfoImg::class);
        $this->infoImgTagRepo = $this->_em->getRepository(InfoImgTag::class);
        $this->serializer = $serializer;
        $this->userClient = $userClient;
    }


    public function getInfoIdsByUser($userId)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $res = $qb->from('App:Info', 'i')
            ->where('i.user_id = :user_id AND i.deletedAt is null')
            ->select('i.id')
            ->setParameters([':user_id' => $userId])
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        $ids = [];
        if (0 < count($res)) {
            foreach ($res as $info) {
                $ids[] = $info['id'];
            }
        }

        return $ids;
    }

    public function countFavByUser($userId)
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->from('App:Info', 'i')
            ->select('sum(i.fav_count)')
            ->where('i.user_id = :user_id AND i.deletedAt is null')
            ->setParameters(['user_id' => $userId])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countColByUser($userId)
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->from('App:Info', 'i')
            ->select('sum(i.col_count)')
            ->where('i.user_id = :user_id AND i.deletedAt is null')
            ->setParameters(['user_id' => $userId])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function list($userId = 0)
    {
        $query = $this->_em->createQueryBuilder()
            ->select('i.id')
            ->from('App:Info', 'i')
            ->where('i.deletedAt is null')
            ->setFirstResult(0)
            ->setMaxResults(2000)
            ->getQuery();
        $ids = $query->getResult();
        shuffle($ids);
        $ids = array_slice($ids, 0, 10);

        $list = [];
        foreach ($ids as $i) {
            $list[] = $this->detail($i['id'], true, $userId);
        }

        return $list;
    }

    public function search($keyword, $relativedId, $page = 1, $pageSize = 10)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('i.id')
            ->from('App:Info', 'i')
            ->orderBy('i.id', 'DESC')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize);
        $keywords = explode(' ', $keyword);
        foreach ($keywords as $k => $v) {
            $query->andWhere('i.content like :keyword'.$k);
            $query->setParameter('keyword'.$k, '%'.$v.'%');
        }
        $query->andWhere('i.deletedAt is null');
        $p = new Paginator($query);
        $p->setUseOutputWalkers(false);

        $list = [];
        foreach ($p as $i) {
            $list[] = $this->detail($i['id'], true, $relativedId);
        }

        return $list;
    }
    
    public function listByUser($userId, $relativedId, $page = 1, $pageSize = 10)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('i.id')
            ->from('App:Info', 'i')
            ->orderBy('i.id', 'DESC')
            ->where('i.user_id = :user_id AND i.deletedAt is null')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->setParameter('user_id', $userId);
        $p = new Paginator($query);
        $p->setUseOutputWalkers(false);

        $list = [];
        foreach ($p as $i) {
            $list[] = $this->detail($i['id'], true, $relativedId);
        }

        return $list;
    }

    public function colList($userId, $relativedId, $page = 1, $pageSize = 10)
    {
        $query = $this->_em->createQueryBuilder();
        $query->select('if.info_id')
            ->from('App:InfoCol', 'if')
            ->where('if.user_id = :user_id AND if.deletedAt is null')
            ->setFirstResult(($page - 1) * $pageSize)
            ->orderBy('if.id', 'DESC')
            ->setMaxResults($pageSize)
            ->setParameter('user_id', $userId);
        $p = new Paginator($query);
        $p->setUseOutputWalkers(false);

        $list = [];
        foreach ($p as $i) {
            $list[] = $this->detail($i['info_id'], true, $relativedId);
        }

        return $list;
    }

    public function detail($id, $asList = true, $userId = 0)
    {
        $qb = $this->_em->createQueryBuilder();
        $cField = $asList ? $qb->expr()->substring('i.content', 1, 50) . ' AS content' : 'i.content';
        $query = $qb->select(
            'i.id, i.user_id, i.ratio, i.lbs_lat, i.lbs_lng, i.lbs_title, i.fav_count, i.col_count',
            $cField,
            'i.createdAt as create_at'
        )
            ->from('App:Info', 'i')
            ->where('i.id = :id AND i.deletedAt is null')
            ->setParameter('id', $id)
            ->getQuery();
        $res = $query->getResult();
        if (1 !== count($res)) {
            throw new \Exception('浪记不存在');
        }
        $info = $this->serializer->toArray($res[0]);
        if ($asList) {
            // 首图
            $im = $this->infoImgRepo->findOneBy(['info_id' => $id], ['id' => 'asc']);
            $img = $im->getImgUrl('thumbnail');
            $info['img'] = $img;
        } else {
            // 处理所有图片和tag
            $imgs = [];
            $ims = $this->infoImgRepo->findBy(['info_id' => $id]);
            foreach ($ims as $i) {
                $tmp = [];
                $tmp['url'] = $i->getImgUrl();
                $tmp['tags'] = [];
                $tagModels = $this->infoImgTagRepo->findBy(['info_img_id' => $i->getId()]);
                if (0 < count($tagModels)) {
                    foreach ($tagModels as $tag) {
                        $tmp['tags'][] = [
                            'x' => $tag->getX(),
                            'y' => $tag->getY(),
                            'content' => $tag->getContent(),
                            'toward' => $tag->getToward(),
                        ];
                    }
                }
                $imgs[] = $tmp;
            }
            $info['imgs'] = $imgs;

            $info['follow_status'] = $this->userClient->call(self::USER_RELATIVE_FOLLOW_STATUS_URL, [
                'user_id' => $userId,
                'relatived_id' => $info['user_id'],
            ]);
        }

        //review count
        $reviewCount = $this->_em->createQueryBuilder()
            ->select('count(r.id)')
            ->from('App:Review', 'r')
            ->where('r.info_id = :info_id AND r.deletedAt is null')
            ->setParameters(['info_id' => $info['id']]);
        $info['review_count'] = (int) $reviewCount->getQuery()->getSingleScalarResult();

        // 处理user info
        $user = $this->userClient->call(self::USER_VIEW_URL, ['id' => $info['user_id']]);
        $info['user'] = [
            'id' => $user['id'],
            'gender' => $user['gender'],
            'username' => $user['username'],
            'avatar' => $user['avatar'],
            'created_at' => $user['created_at'],
            'is_kol' => $user['is_kol'],
            'edu_info' => (array) $user['edu'],
        ];
        // 兼容
        $eduInfo = (array) $user['edu'];
        $info['user']['edu_info']['name'] = $eduInfo['college_name'];
        // 点赞收藏状态
        $info['fav_status'] = (empty($userId) ? false : $this->favStatus($id, $userId)) ? 'yes' : 'no';
        $info['col_status'] = (empty($userId) ? false : $this->colStatus($id, $userId)) ? 'yes' : 'no';

        return $info;
    }

    public function favStatus($id, $userId)
    {
        $m = $this->infoFavRepo->findOneBy([
            'info_id' => $id,
            'user_id' => $userId,
            'deletedAt' => null,
        ]);

        return empty($m) ? false : true;
    }

    public function colStatus($id, $userId)
    {
        $m = $this->infoColRepo->findOneBy([
            'info_id' => $id,
            'user_id' => $userId,
            'deletedAt' => null,
        ]);

        return empty($m) ? false : true;
    }

    public function del($id)
    {
        $info = $this->findOneBy(compact('id'));
        if (empty($info)) {
            throw new \Exception('');
        }
        $this->_em->transactional(function ($em) use ($id, $info) {
            $em->remove($info);
            // InfoImg
            $infoImgs = $this->infoImgRepo->findBy(['info_id' => $id]);
            if (0 < count($infoImgs)) {
                foreach ($infoImgs as $ii) {
                    $em->remove($ii);
                    // InfoImgTag
                    $infoImgTags = $this->infoImgTagRepo->findBy(['info_img_id' => $ii->getId()]);
                    if (0 < count($infoImgTags)) {
                        foreach ($infoImgTags as $iit) {
                            $em->remove($iit);
                        }
                    }
                }
            }
            // InfoFav
            $infoFavs = $this->infoFavRepo->findBy(['info_id' => $id]);
            if (0 < count($infoFavs)) {
                foreach ($infoFavs as $if) {
                    $em->remove($if);
                }
            }
            // InfoCol
            $infoCols = $this->infoColRepo->findBy(['info_id' => $id]);
            if (0 < count($infoCols)) {
                foreach ($infoCols as $if) {
                    $em->remove($if);
                }
            }

            // notify
            $this->userClient->call(self::USER_DEL_NOTIFY_MSG, ['info_id' => $id]);

            $em->flush();
        });

        return true;
    }

    public function fav($infoId, $userId): void
    {
        $info = $this->find($infoId);
        if (empty($info) || $info->isDeleted()) {
            throw new \Exception('info 不存在');
        }
        $infoFav = $this->infoFavRepo->findBy([
            'info_id' => $infoId,
            'user_id' => $userId,
            'deletedAt' => null,
        ]);
        if (0 < count($infoFav)) {
            throw new \Exception('已点赞');
        }
        // add fav & count
        $this->_em->transactional(function ($em) use ($info, $userId) {
            $if = (new InfoFav())->setInfoId($info->getId())
                                 ->setUserId($userId);
            $em->persist($if);
            $info = $info->setFavCount($info->getFavCount() + 1);
            $em->merge($info);

            $em->flush();
        });
    }
    public function unFav($infoId, $userId): void
    {
        $info = $this->find($infoId);
        if (empty($info) || $info->isDeleted()) {
            throw new \Exception('info 不存在');
        }
        $infoFav = $this->infoFavRepo->findOneBy([
            'info_id' => $infoId,
            'user_id' => $userId,
            'deletedAt' => null,
        ]);
        if (empty($infoFav)) {
            throw new \Exception('未点赞');
        }

        // unfav & count
        $this->_em->transactional(function ($em) use ($info, $infoFav) {
            $em->remove($infoFav);
            $info = $info->setFavCount($info->getFavCount()-1);
            $em->merge($info);

            $em->flush();
        });
    }
    public function col($infoId, $userId)
    {
        $info = $this->find($infoId);
        if (empty($info) || $info->isDeleted()) {
            throw new \Exception('info 不存在');
        }
        $infoCol = $this->infoColRepo->findBy([
            'info_id' => $infoId,
            'user_id' => $userId,
            'deletedAt' => null,
        ]);
        if (0 < count($infoCol)) {
            throw new \Exception('已收藏');
        }
        // add col & count
        $this->_em->transactional(function ($em) use ($info, $userId) {
            $if = (new InfoCol())->setInfoId($info->getId())
                                 ->setUserId($userId);
            $em->persist($if);
            $info = $info->setColCount($info->getColCount() + 1);
            $em->merge($info);

            $em->flush();
        });
    }
    public function unCol($infoId, $userId)
    {
        $info = $this->find($infoId);
        if (empty($info) || $info->isDeleted()) {
            throw new \Exception('info 不存在');
        }
        $infoCol = $this->infoColRepo->findOneBy([
            'info_id' => $infoId,
            'user_id' => $userId,
            'deletedAt' => null,
        ]);
        if (empty($infoCol)) {
            throw new \Exception('未收藏');
        }

        // uncol & count
        $this->_em->transactional(function ($em) use ($info, $infoCol) {
            $em->remove($infoCol);
            $info = $info->setColCount($info->getColCount()-1);
            $em->merge($info);

            $em->flush();
        });
    }

    // /**
    //  * @return Info[] Returns an array of Info objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Info
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
