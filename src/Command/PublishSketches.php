<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Api\TelegramApi\TelegramApi;
use App\Entity\Subscription;
use App\TelegramBot\TelegramBot;
use App\HttpApiAdapters\GuzzleHttpAdapter;
use App\Repository\GirlRepository;
use App\Repository\SketchesRepository;
use App\Services\SketchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;
use App\Services\SubscriptionService;
use Exception;

#[AsCommand(
    name: 'app:add',
    hidden: false,
    aliases: ['app:add']
)]
class PublishSketches extends Command
{

    public function __construct(
        private SubscriptionService $subscriptionService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {   
        $array = ['https://rutube.ru/video/30f74d0c065b445e695230cf3e44ea15/', 'https://rutube.ru/video/14bc1375c802712e4f8edea0151ead84/', 'https://rutube.ru/video/f432a8ea3b415fc07ce3c6e49d799fff/', 'https://rutube.ru/video/3093e0fd8e938879ed5732dd29a90f57/', 'https://rutube.ru/video/f3c1e816ad910975739afae6aa2f2957/', 'https://rutube.ru/video/06aebce1bb1e7104906e7f23c5fdd93c/', 'https://rutube.ru/video/2d4bbe9b1478ad273910f86ab01b032e/', 'https://rutube.ru/video/047f55a585c3d52451a7c56871846801/', 'https://rutube.ru/video/f0a5e386be5aa589c5963b5b1bab065d/', 'https://rutube.ru/video/319accbfb0da429ef8d6f4c25ca36426/', 'https://rutube.ru/video/6902b933dc6b1f6c10eed31d22a8f5e1/', 'https://rutube.ru/video/b980c565bd254ccdf4752edde04635a2/', 'https://rutube.ru/video/8940fc3f64e72ba7caba819fb55aff0f/', 'https://rutube.ru/video/eb5edfe4a2d63967599b1c98d937c6a4/', 'https://rutube.ru/video/559fd4889f698519f87b07b3bf571d00/', 'https://rutube.ru/video/709f47b5129b360e6b76f5e78302c522/', 'https://rutube.ru/video/d9e6cbd4b8ed5b050168dc7fca73014a/', 'https://rutube.ru/video/06259309fede193ff598b3dbf3335dbc/', 'https://rutube.ru/video/07cbd5b0ec8c373480c87dfc86eac3f2/', 'https://rutube.ru/video/c5a6dc42bff9f8bdd5ca1097f4324778/', 'https://rutube.ru/video/ed9b44a3d1c4fcdc1bc181b761f7ed8d/', 'https://rutube.ru/video/3a853f8ddb87fe971e9b86ab5a7f30d4/', 'https://rutube.ru/video/67a2d6f21caf4a9bc41a132f3e6c9210/', 'https://rutube.ru/video/47c07d56db9be205fbd334079de174e1/', 'https://rutube.ru/video/e81c183abcd601a5c6ce48fdc7f07326/', 'https://rutube.ru/video/f186bca20744bcf2b56333a564f3bcb6/', 'https://rutube.ru/video/89b26439dd59b2fe00c9da91ad59523b/', 'https://rutube.ru/video/6371a0ee4991871e04a59f8b46f622ed/', 'https://rutube.ru/video/2c53fbed51ec2267739281328674f0c6/', 'https://rutube.ru/video/48006d15705731ace78a1adef4966cd9/', 'https://rutube.ru/video/f5f220094803ffb386a1dc0a2b6f5c3d/', 'https://rutube.ru/video/2184d1dbc3a7f7d38596edb5dc18d627/', 'https://rutube.ru/video/53e7d2fcae365004b121cb47eee2279b/', 'https://rutube.ru/video/a1f3ff12349de332cd1445d6631b9345/', 'https://rutube.ru/video/286199b3951a6d003550f969d76299fb/', 'https://rutube.ru/video/550fdc46c565c4dee524324c8589be2b/', 'https://rutube.ru/video/4a90f6654e65e8dd43fe8027eddf79ef/', 'https://rutube.ru/video/e5c3790f777f45df81f9f9278fe6d878/', 'https://rutube.ru/video/f913b37aed154cf1a12459de81c3022c/', 'https://rutube.ru/video/c139376f2c85e2f549d54a87ef551e99/', 'https://rutube.ru/video/93226c99f48efbad9857cdf42ecb03c9/', 'https://rutube.ru/video/7c96b97953eafa4dbc2c94c49c0981a3/', 'https://rutube.ru/video/0cecc1f20304bfb83e25d77815c21bd5/', 'https://rutube.ru/video/68c41307f8cb1145a4b96efd02444f93/', 'https://rutube.ru/video/74184dc595fc62c4808026e0ac29f02c/', 'https://rutube.ru/video/ca45355cb72c7e7edb7de4d04535cdae/', 'https://rutube.ru/video/f41dc00bf89ed3345d5ed10a314ce394/', 'https://rutube.ru/video/05c78be09679afec07e6931267052707/', 'https://rutube.ru/video/a6023e99d0ab4749c70b84fac0029718/', 'https://rutube.ru/video/f23d6ac685b02c78330d21de80ebeec6/', 'https://rutube.ru/video/6e3ed1b8d754ddb09c7a93fd9f47cbf3/', 'https://rutube.ru/video/e5494ef4db1b03c1e699e986634463bb/', 'https://rutube.ru/video/426d57107cdad68728530c4bc0421260/', 'https://rutube.ru/video/8309ed0433cc24d44fe77dfdf88cd4b0/', 'https://rutube.ru/video/e079e0f6fd6a3db974c13bb0fa8ef75d/', 'https://rutube.ru/video/7a6d91345a1e9fe24fcfe829cc619fb9/', 'https://rutube.ru/video/4c8e0165740a02589b3b8e6e100cac9a/', 'https://rutube.ru/video/49f643c4110eef09eb6e5bbc46e27421/', 'https://rutube.ru/video/d4f441e0f4902e4b636621682d9f12af/', 'https://rutube.ru/video/96b0bfdb6a66e0e2af9ccb702f3ca816/', 'https://rutube.ru/video/b8fd110dc1d8a202ff9220d8ea3cb262/', 'https://rutube.ru/video/3ee03ea8ca5b4f43a2ff81fa7f14ab98/', 'https://rutube.ru/video/669d042ade5461ca81199963dba7d6f5/', 'https://rutube.ru/video/4f80837afa7da668eaaf24b34992c911/', 'https://rutube.ru/video/00e4794dbdbfad36f651abc5a27da949/', 'https://rutube.ru/video/beb607b54972e3eeb26b3c602192d875/', 'https://rutube.ru/video/31a42e0f895b4507597769fc25f0a7e6/', 'https://rutube.ru/video/13ef73f137fff00077d3f26cdfc1ab30/', 'https://rutube.ru/video/206290f45abaadbfcda8f683769eedef/', 'https://rutube.ru/video/0817e0f4fd71d48d66d2d15746f0f2d9/'];
        $this->subscriptionService->publishSketches($array, 'Наша Раша! Славик и Димон', 'ochkoshnik');
        return Command::SUCCESS;
    }
}