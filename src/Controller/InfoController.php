<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\InfoImg;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Info;
use App\Entity\InfoImgTag;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\InfoRepository;
use OldSound\RabbitMqBundle\RabbitMq\RpcClient;
use App\Service\MockClient;
use App\Entity\Report;
use Symfony\Component\Config\Definition\Exception\Exception;

class InfoController extends AbstractController
{
    private $em;
    /**
     * @var InfoRepository
     */
    private $infoRepo;

    private $reportRepo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->infoRepo = $this->em->getRepository(Info::class);
        $this->reportRepo = $this->em->getRepository(Report::class);
    }

    /**
     * @Route("/info/list", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="信息流列表")
     * @SWG\Tag(name="info")
     */
    public function list(Request $r)
    {
        $userId = empty($r->get('user_id')) ? 0 : $r->get('user_id');
        $list = $this->infoRepo->list($userId);

        return new JsonResponse($list);
    }

    /**
     * @Route("/info/list-by-user", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="relatived_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="信息流列表")
     * @SWG\Tag(name="info")
     */
    public function listByUser(Request $r)
    {
        $list = $this->infoRepo->listByUser(
            $r->get('user_id'),
            $r->get('relatived_id', 0),
            $r->get('page', 1),
            $r->get('pagesize', 10)
        );

        return new JsonResponse($list);
    }

    /**
     * @Route("/info/search", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="keyword", type="string", example="哈佛"),
     *          @SWG\Property(property="relatived_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="信息流搜索")
     * @SWG\Tag(name="info")
     */
    public function search(Request $r)
    {
        $list = $this->infoRepo->search(
            $r->get('keyword'),
            $r->get('relatived_id', 0),
            $r->get('page', 1),
            $r->get('pagesize', 10)
        );

        return new JsonResponse($list);
    }
    /**
     * @Route("/info/col-list", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="relatived_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="收藏列表")
     * @SWG\Tag(name="info")
     */
    public function colList(Request $r)
    {
        $list = $this->infoRepo->colList(
            $r->get('user_id'),
            $r->get('relatived_id', 0),
            $r->get('page', 1),
            $r->get('pagesize', 10)
        );

        return new JsonResponse($list);
    }
    /**
     * @Route("/info/detail", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="信息流详情")
     * @SWG\Tag(name="info")
     */
    public function detail(Request $r)
    {
        $detail = $this->infoRepo->detail($r->get('info_id'), false, $r->get('user_id'));
        
        return new JsonResponse($detail);
    }
    /**
     * @Route("/info/new", methods={"POST"})
     *
     * @SWG\Response(response=200, description="新增信息")
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="ratio", type="string", example="3:4"),
     *          @SWG\Property(property="content", type="text", example="111111111"),
     *          @SWG\Property(property="lbs_title", type="string", example="111111111"),
     *          @SWG\Property(property="lbs_lat", type="double", example="111111111"),
     *          @SWG\Property(property="lbs_lng", type="double", example="111111111"),
     *          @SWG\Property(property="imgs", type="array",
     *              @SWG\Items(type="object",
     *                  @SWG\Property(property="path", type="string", example="a/b/c.jpg"),
     *                  @SWG\Property(property="tags", type="array",
     *                      @SWG\Items(type="object",
     *                          @SWG\Property(property="x", type="string", example="0.324"),
     *                          @SWG\Property(property="y", type="string", example="0.324"),
     *                          @SWG\Property(property="content", type="string", example="hahaha"),
     *                      )
     *                  )
     *              )
     *          ),
     *      )
     * )
     * @SWG\Tag(name="info")
     */
    public function new(Request $r)
    {
        $info = new Info();
        // 事务开始
        $this->em->transactional(function ($em) use ($r, $info) {
            if (empty($r->get('content'))) {
                throw new \Exception('信息流内容不能为空');
            }
            $info->setUserId($r->get('user_id'))
                 ->setRatio($r->get('ratio'))
                 ->setContent($r->get('content'))
                 ->setColCount(0)
                 ->setFavCount(0)
                 ->setLbsLat(empty($r->get('lbs_lat')) ? null : $r->get('lbs_lat'))
                 ->setLbsLat(empty($r->get('lbs_lng')) ? null : $r->get('lbs_lng'))
                 ->setLbsTitle($r->get('lbs_title'));
            $em->persist($info);
            $em->flush();
            // 存入图片
            if (!is_array($r->get('imgs')) || 0 === count($r->get('imgs'))) {
                throw new \Exception('请上传图片');
            }
            foreach ($r->get('imgs') as $img) {
                $ii = new InfoImg();
                $ii->setInfoId($info->getId())
                   ->setPath($img['path']);
                $em->persist($ii);
                $em->flush();

                if (!is_array($img['tags']) || 0 === count($img['tags'])) {
                    continue;
                }
                // 存入图片tags
                foreach ($img['tags'] as $tag) {
                    $iit = new InfoImgTag();
                    $tag['toward'] = $tag['toward'] ?? 'LEFT';
                    $tag['toward'] = ('LEFT' !== $tag['toward'] && 'RIGHT' !== $tag['toward'])
                        ? 'LEFT' : $tag['toward'];
                    $iit->setInfoImgId($ii->getId())
                        ->setX($tag['x'])
                        ->setY($tag['y'])
                        ->setToward($tag['toward'])
                        ->setContent($tag['content']);
                    $em->persist($iit);
                }
            }
            $em->flush();
        });
        return new JsonResponse(['result' => 'ok', 'info_id' => $info->getId()]);
    }

    /**
     * @Route("/info/edit", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *          @SWG\Property(property="content", type="string", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="编辑")
     * @SWG\Tag(name="info")
     */
    public function edit(Request $r)
    {
        $info = $this->infoRepo->find($r->get('info_id'));
        if (empty($info)) {
            throw \Exception('浪记不存在');
        }
        $info->setContent($r->get('content'));
        $this->em->merge($info);
        $this->em->flush();
        return new JsonResponse(['result' => 'ok']);
    }


    /**
     * @Route("/info/del", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="点赞")
     * @SWG\Tag(name="info")
     */
    public function del(Request $r)
    {
        $this->infoRepo->del($r->get('info_id'));

        return new JsonResponse(['result' => 'ok']);
    }

    /**
     * @Route("/info/fav", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="点赞")
     * @SWG\Tag(name="info")
     */
    public function fav(Request $r)
    {
        $this->infoRepo->fav($r->get('info_id'), $r->get('user_id'));

        return new JsonResponse(['result' => 'ok']);
    }

    /**
     * @Route("/info/un-fav", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="取消点赞")
     * @SWG\Tag(name="info")
     */
    public function unFav(Request $r)
    {
        $this->infoRepo->unFav($r->get('info_id'), $r->get('user_id'));

        return new JsonResponse(['result' => 'ok']);
    }

    /**
     * @Route("/info/col", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="点赞")
     * @SWG\Tag(name="info")
     */
    public function col(Request $r)
    {
        $this->infoRepo->col($r->get('info_id'), $r->get('user_id'));

        return new JsonResponse(['result' => 'ok']);
    }

    /**
     * @Route("/info/un-col", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="info_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="取消点赞")
     * @SWG\Tag(name="info")
     */
    public function unCol(Request $r)
    {
        $this->infoRepo->unCol($r->get('info_id'), $r->get('user_id'));

        return new JsonResponse(['result' => 'ok']);
    }

    /**
     * @Route("/info/col-count", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="收藏数")
     * @SWG\Tag(name="info")
     */
    public function colCount(Request $r)
    {
        $count = $this->infoRepo->countColByUser($r->get('user_id'));

        return new JsonResponse(compact('count'));
    }

    /**
     * @Route("/info/fav-count", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *      )
     * )
     * @SWG\Response(response=200, description="点赞数")
     * @SWG\Tag(name="info")
     */
    public function favCount(Request $r)
    {
        $count = $this->infoRepo->countFavByUser($r->get('user_id'));

        return new JsonResponse(compact('count'));
    }

    /**
     * @Route("/report", methods={"POST"})
     * @SWG\Parameter(
     *      name="body", in="body",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="user_id", type="integer", example="123"),
     *          @SWG\Property(property="category", type="string", example="INFO"),
     *          @SWG\Property(property="item_id", type="integer", example="123"),
     *          @SWG\Property(property="type", type="string", example="OTHER"),
     *          @SWG\Property(property="content", type="string", example="hhhhh"),
     *      )
     * )
     * @SWG\Response(response=200, description="点赞数")
     * @SWG\Tag(name="info")
     */
    public function report(Request $r)
    {
        $this->reportRepo->add(
            $r->get('user_id'),
            $r->get('category'),
            $r->get('item_id'),
            $r->get('type'),
            $r->get('content')
        );

        return new JsonResponse(['result' => 'ok']);
    }

    
    /**
     * t
     * @Route("t", methods={"GET", "POST"})
     */
    public function t(RpcClient $rpcClient, MockClient $mockClient)
    {
        // $req = Request::create('/user/view', 'POST', ['id' => 8, 'scope' => 'detail']);
        // $kernel = new \App\Kernel('env', true);
        // $res = $kernel->handle($req);

        // return $res;

        // $rpcClient->addRequest(json_encode([
        //     'route' => '/user/view',
        //     'request' => ['id' => 8, 'scope' => 'detail'],
        // ]), 'user', 't');
        // return new JsonResponse($rpcClient->getReplies()['t']);

        // $res = $mockClient->request('user', '/force-stop', ['id' => 8]);
        $res = $mockClient->request('user', '/user/view', ['id' => 8]);

        return new JsonResponse($res);
    }
}
