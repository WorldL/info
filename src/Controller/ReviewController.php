<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\InfoImg;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Info;
use App\Entity\Review;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\InfoRepository;
use JMS\Serializer\SerializerInterface;

class ReviewController extends AbstractController
{
    private $em;
    /**
     * @var \App\Repository\InfoRepository $infoRepo
     */
    private $infoRepo;

    /**
     * @var \App\Repository\ReviewRepository $reviewRepo
     */
    private $reviewRepo;

    /**
     * @var \JMS\Serializer\Serializer $serializer
     */
    private $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->infoRepo = $this->em->getRepository(Info::class);
        $this->reviewRepo = $this->em->getRepository(Review::class);
        $this->serializer = $serializer;
    }
    /**
     * @Route("/review/reply", methods={"POST"})
     *
     * @SWG\Response(response=200, description="回复")
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *          @SWG\Property(property="content", type="text", example="111111111"),
     *          @SWG\Property(property="reply_to", type="integer", example="123"),
     *          @SWG\Property(property="pid", type="integer", example="123"),
     *      )
     * )
     * @SWG\Tag(name="review")
     */
    public function reply(Request $r)
    {
        $res = $this->reviewRepo->reply(
            $r->get('info_id'),
            $r->get('user_id'),
            $r->get('content'),
            $r->get('reply_to'),
            $r->get('pid', 0)
        );

        return new JsonResponse(['result' => 'ok']);
    }

    /**
     * @Route("/review/detail", methods={"POST"})
     *
     * @SWG\Response(response=200, description="删除评论")
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="review_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Tag(name="review")
     */
    public function detail(Request $r)
    {
        $res = $this->reviewRepo->find(
            $r->get('review_id')
        );

        return new JsonResponse($this->serializer->toArray($res));
    }

    /**
     * @Route("/review/del", methods={"POST"})
     *
     * @SWG\Response(response=200, description="删除评论")
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="review_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Tag(name="review")
     */
    public function del(Request $r)
    {
        $res = $this->reviewRepo->del(
            $r->get('review_id')
        );

        return new JsonResponse(['result' => 'ok']);
    }

    /**
     * @Route("/review/fav", methods={"POST"})
     *
     * @SWG\Response(response=200, description="回复点赞")
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="review_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Tag(name="review")
     */
    public function fav(Request $r)
    {
        $res = $this->reviewRepo->fav(
            $r->get('review_id'),
            $r->get('user_id')
        );

        return new JsonResponse(['result' => 'ok']);
    }

    /**
     * @Route("/review/un-fav", methods={"POST"})
     *
     * @SWG\Response(response=200, description="回复取消点赞")
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="review_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Tag(name="review")
     */
    public function unFav(Request $r)
    {
        $res = $this->reviewRepo->unFav(
            $r->get('review_id'),
            $r->get('user_id')
        );

        return new JsonResponse(['result' => 'ok']);
    }
    /**
     * @Route("/review/list", methods={"POST"})
     *
     * @SWG\Response(response=200, description="回复")
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *          @SWG\Property(property="relatived_id", type="integer", example="123"),
     *          @SWG\Property(property="page", type="integer", example="123"),
     *          @SWG\Property(property="pagesize", type="integer", example="123"),
     *      )
     * )
     * @SWG\Tag(name="review")
     */
    public function list(Request $r)
    {
        $res = $this->reviewRepo->list(
            $r->get('info_id'),
            $r->get('relatived_id', 0),
            $r->get('page', 1),
            $r->get('pagesize', 10)
        );

        return new JsonResponse($res);
    }

    /**
     * @Route("/review/list-by-pid", methods={"POST"})
     *
     * @SWG\Response(response=200, description="回复")
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="pid", type="integer", example="123"),
     *          @SWG\Property(property="relatived_id", type="integer", example="123"),
     *          @SWG\Property(property="page", type="integer", example="123"),
     *          @SWG\Property(property="pagesize", type="integer", example="123"),
     *      )
     * )
     * @SWG\Tag(name="review")
     */
    public function listByPid(Request $r)
    {
        $res = $this->reviewRepo->listByPid(
            $r->get('pid'),
            $r->get('relatived_id', 0),
            $r->get('page', 1),
            $r->get('pagesize', 10)
        );

        return new JsonResponse($res);
    }
}
