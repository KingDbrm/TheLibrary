<?php

declare(strict_types=1);

namespace block;

use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\crafting\CraftingManagerFromDataHelper;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Path;
use const pocketmine\BEDROCK_DATA_PATH;

final class BlockRegistry {

    public static function override(
        Block   $block,
        string  $blockName,
        ?string $vanillaItemsReference = null
    ) : void {
        if (!str_starts_with($blockName, "minecraft:")) {
			throw new InvalidArgumentException("The block name is invalid.");
		}
		if ($vanillaItemsReference === null) {
			$vanillaItemsReference = explode(":", $blockName)[1];
		}

		$reflection = new \ReflectionClass(VanillaItems::class);
		/** @var array<string, Block> $blocks */
		$blocks = $reflection->getStaticPropertyValue("members");
		$blocks[mb_strtoupper($vanillaItemsReference)] = clone $block;
		$reflection->setStaticPropertyValue("members", $blocks);

		(function(string $id, \Closure $deserializer) : void {
			/**
			 * @var ItemDeserializer $this
			 */
			$this->deserializers[$id] = $deserializer;
		})->call(
			GlobalItemDataHandlers::getDeserializer(),
			$blockName,
			fn(SavedItemData $data) => $block->asItem()
		);
		(function(Block $block, \Closure $serializer) : void {
			/**
			 * @var ItemSerializer $this
			 */
			$this->blockItemSerializers[$block->getTypeId()] = $serializer;
		})->call(
			GlobalItemDataHandlers::getSerializer(),
			$block,
			fn() => new SavedItemData($blockName)
		);

		(function() : void {
			/**
			 * @var Server $this
			 */
			$this->craftingManager = CraftingManagerFromDataHelper::make(Path::join(BEDROCK_DATA_PATH, "recipes"));
		})->call(Server::getInstance());
    }

    /**
	 * @param string[] $stringToItemParserNames
	 */
	private static function register(string $id, Item $item, array $stringToItemParserNames) : void{
		GlobalItemDataHandlers::getDeserializer()->map($id, fn() => clone $item);
		GlobalItemDataHandlers::getSerializer()->map($item, fn() => new SavedItemData($id));

		foreach($stringToItemParserNames as $name){
			StringToItemParser::getInstance()->register($name, fn() => clone $item);
		}
    }
}