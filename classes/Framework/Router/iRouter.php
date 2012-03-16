<?php
namespace Core\Framework\Router;
interface iRouter {
  public function getParams();
  public function getAction();
  public function setFrontController(\Core\Framework\FrontController $frontController);
}
?>