<?php

declare(strict_types=1);

namespace block;

use pocketmine\block\Block;
use pocketmine\world\Position;
use pocketmine\world\World;

final class BlockParser {
	public static function getBlockHash(Block $block): string {
		return ($position = $block->getPosition())->getWorld()->getFolderName() . ':' . World::blockHash(
			(int) $position->getX(),
			(int) $position->getY(),
			(int) $position->getZ()
		);
	}

	public static function getBlockHashByPosition(Position $position): string {
		return $position->getWorld()->getFolderName() . ':' . World::blockHash(
			(int) $position->getX(),
			(int) $position->getY(),
			(int) $position->getZ()
		);
	}

	public static function getBlockHashFrom(World $world, int $x, int $y, int $z): string {
		return ($world->getFolderName() . ':' . World::blockHash($x, $y, $z));
	}
}
