<?php

namespace App\Server;

use Exception;
use GuzzleHttp\Client;
use App\TelegramBot\TelegramBot;
use App\TelegramBotRequest\TelegramBotRequest;
use App\Database\FileSystemBotDb;
use App\Buttons\ButtonService;
use App\Api\WheatherApiAdapters\WheatherApiFree;
use App\Api\WheatherApiAdapters\Wheather;
use App\Api\TelegramApi\TelegramApi;
use App\HttpApiAdapters\GuzzleHttpAdapter;
use App\Repository\SketchesRepository;
use App\Services\SubscriptionService;
use App\Services\GirlService;
use App\Services\SketchService;
use App\Api\VkApi\VkApi;
use App\Entity\Girl;
use App\Repository\GirlRepository;
use Symfony\Contracts\Service\Attribute\SubscribedService;

class OldServer {
    private $tgBot;
    private $request;
    private $chatID;
    
    public function __construct(
        private SubscriptionService $subscriptionService,
        private SketchService $skecthService,
        private GirlService $girlService,
        ) 
    {
        $this->tgBot = new TelegramBot(
            new TelegramApi((new GuzzleHttpAdapter('https://api.telegram.org/bot6768896921:AAHSiWv6mmLSdd6b7kLVOIy9XXKltN8KIlg/')))
        );
    }

    public function handleRequest()
    {
        $request = json_decode(file_get_contents('php://input'), true);

        if(array_key_exists('poll', $request) || array_key_exists('poll_answer', $request)) {
            return;
        }

        $this->request = new TelegramBotRequest($request);
        $this->chatID = (string) $this->request->getChatID();
        $command = explode(' ', $this->request->text);

        if (!count($command)) {
            $this->tgBot->api->sendMessage($this->chatID, "Команда неверного формата");
            return;
        }

        if ($command[0] === '/girl') {
            try {
                $girl = $this->girlService->getGirlInfoById((int) $command[1]);

                if(!$girl) {
                    $this->tgBot->api->sendMessage($this->chatID, "По данному запросу ничего не найдено");
                    return;
                }
    
                $this->subscriptionService->sendGirlsPoll($this->chatID, $girl, 1);
                return;
            } catch (Exception $e) {
                $this->tgBot->api->sendMessage($this->chatID, "Ой, что-то пошло не так");
                $this->tgBot->api->sendMessage(SubscriptionService::TEST_CHAT_ID, $e->getMessage());
                return;
            }
        }

        try {
            if(count($command) < 3) {
                $this->tgBot->api->sendMessage($this->chatID, "Команда неверного формата");
                return;
            }
    
            [$command, $sketchName, $series] = $command;
    
            if ($command !== '/show') {
                $this->tgBot->api->sendMessage($this->chatID, "Такой команды не существует");
                return;
            }
    
            $sketch = $this->skecthService->getSketchLink($sketchName, $series);
            
            if (!$sketch) {
                $this->tgBot->api->sendMessage($this->chatID, "Такого видео не существует");
                return;
            }
            $this->tgBot->api->sendMessage($this->chatID, $sketch);
            return;
        } catch (Exception $e) {
            $this->tgBot->api->sendMessage($this->chatID, "Ой, что-то пошло не так");
            $this->tgBot->api->sendMessage(SubscriptionService::TEST_CHAT_ID, $e->getMessage());
            return;
        }
    }

        // return;
        // $isNewMember = !$this->usersRepository->exists($this->chatID);

        // if ($isNewMember) {
        //     // $this->actionNewMember();
        //     $this->usersRepository->create($this->request);
        // }

        // if (in_array($this->request->chatInstance, ["-3564527224321338473","-2137094095897596224"])) {
        //     $this->tgBot->api->sendMessage($this->chatID, $this->someArray[60]);
        //     return;
        // }

        // // try {
        // //     // $db = new PostgresDB();
        // //     // $res = $db->executeQuery("INSERT INTO users VALUES (3, 'yuri_2', 'yuri_0494', 'private', 'ru');");
        // // } catch(Exception $e) {
        // //     $res = $e;
        // //     return $e;
        // // }
        // if($this->request->isMessage) {
        //     $lastCommand = $this->tgBot->db->getLastCommandByChatId($this->chatID);
        //     if($lastCommand === '/wheather') {
        //         $this->actionGetWheatherInfo();
        //     }
        // }
    
        // if($this->request->isCallback) {

        //     if($this->request->command === '/start') {
        //         $this->actionStart();
        //     }
        
        //     // if($this->request->command === '/gif') {
        //     //     $this->actionGif();
        //     // }

        //     if($this->request->command === '/wheather') {
        //         $this->actionWheather();
        //     }

        //     // if($this->request->command === '/sneaker-shop') {
        //     //     $this->actionSneakerShop();
        //     // }

        //     // if($this->request->command === '/goods') {
        //     //     $this->actionGoods();
        //     // }

        //     // if($this->request->command === '/cart') {
        //     //     $this->actionCart();
        //     // }
            
        // }

        // if($this->request->isMyChatMember) {
        //     if($this->request->chatMemberStatus === 'kicked') {
        //         $this->tgBot->db->unsetChatId($this->chatID);
        //     }
        // }

    // private function actionNewMember()
    // {
    //     try {
    //         $this->tgBot->db->setChatId($this->chatID);
    //         $this->tgBot->api->sendMessage($this->chatID, 'Что будем делать?', ['reply_markup' => ButtonService::getInlineKeyboardForStart()]);
    //     } catch (Exception $e) {
    //         $this->tgBot->api->sendMessage($this->chatID, 'Ой, что-то пошло не так', ['reply_markup' => ButtonService::getInlineKeyboardForStart()]);
    //     }
    // }

    // private function actionStart()
    // {
    //     try {
    //         $this->tgBot->db->setLastCommandByChatId($this->chatID, '/start');
    //         $this->tgBot->api->sendMessage($this->chatID, 'Что будем делать?', ['reply_markup' => ButtonService::getInlineKeyboardForStart()]);
    //     } catch (Exception $e) {
    //         $this->tgBot->api->sendMessage($this->chatID, 'Ой, что-то пошло не так', ['reply_markup' => ButtonService::getInlineKeyboardForStart()]);
    //     }
    // }

    // private function actionGif()
    // {
    //     try {
    //         $this->tgBot->db->setLastCommandByChatId($this->chatID, '/gif');
    //         $this->tgBot->api->sendMessage($this->chatID, 'Получите случайную гифку!', ['reply_markup' => ButtonService::getInlineKeyboardForGif()]);
    //     } catch (Exception $e) {
    //         $this->tgBot->api->sendMessage($this->chatID, 'Ой, что-то пошло не так', ['reply_markup' => ButtonService::getInlineKeyboardForStart()]);
    //     }
    // }

    // private function actionWheather()
    // {
    //     try {
    //         $this->tgBot->db->setLastCommandByChatId($this->chatID, '/wheather');
    //         $this->tgBot->api->sendMessage($this->chatID, 'Погода в каком городе вас интересует (отправьте название города в сообщении)', ['reply_markup' => ButtonService::getInlineKeyboardForGif()]);
    //     } catch (Exception $e) {
    //         $this->tgBot->api->sendMessage($this->chatID, 'Ой, что-то пошло не так', ['reply_markup' => ButtonService::getInlineKeyboardForStart()]);
    //     }
    // }

    // private function actionGetWheatherInfo()
    // {
    //     try {
    //         $wheather = Wheather::createByWAFData((new WheatherApiFree())->getWheatherInfo($this->request->text));
    //         $this->tgBot->api->sendMessage(
    //             $this->chatID, 
    //             $wheather->toMessage($this->request->text), 
    //             ['reply_markup' => ButtonService::getInlineKeyboardForWheather()]
    //         );
    //     } catch (Exception $e) {
    //         $this->tgBot->api->sendMessage(
    //             $this->chatID, 
    //             'Не удалось узнать прогноз погоды для ' . $this->request->text . '. Причина: ' . $e, 
    //             ['reply_markup' => ButtonService::getInlineKeyboardForWheather()]
    //         );
    //     }
    // }

    // private function actionSneakerShop()
    // {
    //     try {
    //         $this->tgBot->api->sendMessage(
    //             $this->chatID,
    //             'Добро пожаловать в наш магазин', 
    //             ['reply_markup' => ButtonService::getInlineKeyboardForSneaker()]
    //         );
    //     } catch (Exception $e) {
    //         $this->tgBot->api->sendMessage(
    //             $this->chatID, 
    //             'Возникла проблема с загрузкой магазина', 
    //             ['reply_markup' => ButtonService::getInlineKeyboardForWheather()]
    //         );
    //     }
    // }

    // private function actionGoods()
    // {
    //     try {
    //         $this->tgBot->api->sendMessage(
    //             $this->chatID,
    //             'Список товаров', 
    //             ['reply_markup' => ButtonService::getInlineKeyboardForGoods()]
    //         );
    //     } catch (Exception $e) {
    //         $this->tgBot->api->sendMessage(
    //             $this->chatID, 
    //             'Возникла проблема с загрузкой магазина', 
    //             ['reply_markup' => ButtonService::getInlineKeyboardForWheather()]
    //         );
    //     }
    // }

    // private function actionCart()
    // {
    //     try {
    //         $this->tgBot->api->sendMessage(
    //             $this->chatID,
    //             'Корзина', 
    //             ['reply_markup' => ButtonService::getInlineKeyboardForCart()]
    //         );
    //     } catch (Exception $e) {
    //         $this->tgBot->api->sendMessage(
    //             $this->chatID, 
    //             'Возникла проблема с загрузкой магазина', 
    //             ['reply_markup' => ButtonService::getInlineKeyboardForWheather()]
    //         );
    //     }
    // }
}