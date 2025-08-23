<?php

declare(strict_types=1);

namespace block;

use pocketmine\inventory\CreativeCategory;
use pocketmine\inventory\CreativeGroup;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;

class AddInCreative {

    public function __construct(private CreativeCategory $category = CreativeCategory::ITEMS, private ?CreativeGroup $group = null) {

    }

    public function addInCreative(Item $item): void {
        CreativeInventory::getInstance()->add($item, $this->category, $this->group);
    }
}