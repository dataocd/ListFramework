<?php
namespace Core\Framework\Dispatcher;
interface iDispatcher {
  public function getController();
  public function getParams();
  public function getAction();
  public function setRouter($router);
}
?>