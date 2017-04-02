<?php

namespace VehturiinikShopBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('checkType', array($this, 'typeFilter')),
        );
    }

    public function typeFilter($type)
    {
        switch ($type){
            case 'notice':
                $type = 'success';
                break;
            case 'error':
                $type = 'danger';
                break;
            default:
                $type = 'warning';
        }

        return $type;
    }
}