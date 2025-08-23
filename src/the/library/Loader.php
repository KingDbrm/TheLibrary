<?php

declare(strict_types=1);

namespace the\library;

use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use rajadordev\smartcommand\api\SmartCommandAPI;
use Ramsey\Uuid\Uuid;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class Loader extends PluginBase {
    use SingletonTrait;

    public function onLoad(): void {
		self::setInstance($this);
        $this->getServer()->getLoader()->addPath('', $this->getFile() . 'libs');
    }

    public function onEnable(): void {
        $this->loadLibraries();
    }

    public function loadLibraries(): void {
        if (!SmartCommandAPI::isRegistered()) SmartCommandAPI::register($this);
        $this->registerInvMenu();
    }

    public function registerInvMenu(): void {
        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        try {
            CustomSizedInvMenu::init($this);
        } catch (RuntimeException $e){
            $texture = $this->getResourcePath("InventoryUIResourcePack.zip");
            $path = $this->getServer()->getResourcePackManager()->getPath();
            $dest = $path . "InventoryUIResourcePack.zip";
            if (!file_exists($dest)) {
                @mkdir(dirname($dest), 0777, true);
                copy($texture, $dest);
            }

            $configPath = $path . "resource_packs.yml";
            $rpConfig = new Config($configPath, Config::YAML);
            $rpConfig->set("force_resources", true);

            $stack = $rpConfig->get("resource_stack", []);
            if (!in_array("InventoryUIResourcePack.zip", $stack)) {
                $stack[] = "InventoryUIResourcePack.zip";
                $rpConfig->set("resource_stack", $stack);
                $rpConfig->save();
            }
            try {
                CustomSizedInvMenu::init($this);
            } catch (RuntimeException $e){
                $this->getServer()->shutdown();
            }
        }
    }

    private function reloadPlugin(string $pluginName): bool {
        $manager = $this->getServer()->getPluginManager();
        $plugin = $manager->getPlugin($pluginName);
        if ($plugin instanceof Plugin){
            $manager->disablePlugin($plugin);
            $manager->enablePlugin($plugin);
            return true;
        }
        return false;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "library"){
            if (isset($args[0])){
                if ($args[0] === "reload"){
                    if (isset($args[1])){
                        if ($args[1] === "libraries"){
                            $this->loadLibraries();
                            $sender->sendMessage("§aLibraries registered!");
                        } elseif ($args[1] === "plugin") {
                            if (isset($args[2])){
                                if ($args[2] !== "TheLibrary"){
                                    if ($this->reloadPlugin($args[2])){
                                        $sender->sendMessage("§aPlugin §f{$args[2]} §areloaded successfully!");
                                    } else {
                                        $sender->sendMessage("§cThe §f{$args[2]} §cisn't are a plugin");
                                    }
                                } else {
                                    $sender->sendMessage("§cYou can't reload plugin: §fTheLibrary");
                                }
                            }
                        } else {
                            $sender->sendMessage($this->getUsageMessage());
                        }
                    } else {
                        $sender->sendMessage($this->getUsageMessage());
                    }
                } else {
                    $sender->sendMessage($this->getUsageMessage());
                }
            } else {
                $sender->sendMessage($this->getUsageMessage());
            }
        }
        return true;
    }

    public function getUsageMessage(): string {
        return implode("\n", [
            "§l§eHELP OF THE LIBRARY COMMANDS",
            "§r§0- §7Use: §f/library reload plugin [plugin-name] §7to turn off and on a plugin",
            "§r§0- §7Use: §f/library reload libraries §7to register all libraries",
        ]);
    }

    /**
     * @param string $path
     * @param string $rpacks
     */
    private function makeTexture(string $path, string $rpacks){
        /*modify uuids to force changes to players*/
        $config = new Config($path."manifest.json", Config::JSON, []);
        $header = $config->get("header", []);
        $header["uuid"] = Uuid::uuid4()->toString();
        $header["name"] = "Teste".rand(0, 10000);
        $modules = $config->get("modules", []);
        $modules["uuid"] = Uuid::uuid4()->toString();
        $config->set("header", $header);
        $config->set("modules", $modules);
        $config->save();
        $config->reload();

        /*make a zip*/
        $rootPath = realpath($path);
        $zip = new \ZipArchive();
        $zip->open($rpacks.'InventoryUIResourcePack.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
            else {
                $end2 = substr($file,-2);
                if ($end2 == "/.") {
                    $folder = substr($file, 0, -2);
                    try {
                        $zip->addEmptyDir($folder);
                    }catch (\Exception $e){
                        echo "lixo\n";
                    }
                }
            }
        }
        try {
            $zip->close();
        }catch (\Exception $e){
            echo "ss\n";
        }
    }
}