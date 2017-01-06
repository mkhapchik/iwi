<?php
namespace Application;

interface TreeObjectInterface
{
    public function getParentId();
    public function getId();
    public function setChildren($children);
}