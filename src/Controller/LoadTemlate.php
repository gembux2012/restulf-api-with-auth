<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 08.11.19
 * Time: 14:05
 */

namespace App\Controller ;


use PhpParser\Node\Expr\Array_;
use Psr\Http\Message\ServerRequestInterface;

class LoadTemlate extends Controller
{
    /*(
    public function __construct(FilesystemInterface $filesystem)
    {
      //  parent::__construct( $filesystem);

    }

    /**
     * @param ServerRequestInterface $request
     * @return Response|PromiseInterface
     */
    public function __invoke(ServerRequestInterface $request, string $name)
    {
        $realPath = ROOT_PATH_TEMPLATES.DS.$name.'.html';
        return $this->makeResponseFromFile( $realPath);
        //        return  JsonResponse::ok("hellow I server");
    }

}