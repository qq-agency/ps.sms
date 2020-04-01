<?php

namespace Ps\Sms\Model;

class SenderCollection extends \ArrayIterator
{
    public function toArray()
    {
        $list = [];
        foreach ($this->getArrayCopy() as $item) {
            $list[] = [
                'id' => $item->getName(),
                'name' => $item->getName()
            ];
        }

        return $list;
    }
}
