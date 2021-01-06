<?php

namespace App\Command;

use DateTime;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Product;
use Symfony\Component\Uid\Uuid;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CreateOrderFromXmlFileCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private  $io;
    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * @var string
     */
    private $dataDirectory;

    private $userRepository;

    protected static $defaultName = 'app:create-orders-from-xml';

    public function __construct(EntityManagerInterface $entityManager, string $dataDirectory, UserRepository $userRepository)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->dataDirectory = $dataDirectory;
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Importer des donner en provenance d\'un fichier xml')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createOrders();
        return Command::SUCCESS;
    }

    private function getDataFromFile(): array
    {
        $file = $this->dataDirectory . 'data.xml';
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        $encoders = [new XmlEncoder(), new JsonEncode()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $fileString = \file_get_contents($file);

        $data = $serializer->decode($fileString, $fileExtension);

        return $data['orders'];
    }

    private function createOrders(): void
    {

        $this->io->section('CREATION DES ORDERS A PARTIR DU FICHIER XML');

        $ordersCreated = 0;

        foreach ($this->getDataFromFile()  as $key => $orders) {
            foreach ($orders as $arrOrder) {

                $user = new User();
                $address = new Address();
                $product = new Product();

                $address->setCity($arrOrder['user']['address']['city']);
                $address->setStreet($arrOrder['user']['address']['street']);
                $address->setZip($arrOrder['user']['address']['zip']);

                $this->entityManager->persist($address);

                $product->setLabel($arrOrder['product']['label']);
                $product->setPrice($arrOrder['product']['price']['#']);
                $product->setReference($arrOrder['product']['ref']);
                $product->setQuantity($arrOrder['product']['@qte']);

                $this->entityManager->persist($product);

                $user->setEmail($arrOrder['user']['email']);
                $user->setFirstname($arrOrder['user']['firstname']);
                $user->setLastname($arrOrder['user']['lastname']);
                $user->addAddress($address);

                $this->entityManager->persist($user);


                $order = new Order();

                $order->setId(Uuid::fromString('d9e7a184-5d5b-11ea-a62a-3499710062d0'));
                $order->setCreatedAt(new DateTime($arrOrder['created_at']));
                $order->setMarketplace($arrOrder['marketplace']);

                $order->setProduct($product);
                $order->setUser($user);

                $this->entityManager->persist($order);

                $ordersCreated++;
            }
        }

        $this->entityManager->flush();

        if ($ordersCreated >= 1)
            $string = "{$ordersCreated} Orders CREES EN BASE DE DONNEE.";
        else
            $string = "AUCUN ORDER N'A ETE CREE EN BASE DE DONNEE.";


        $this->io->success($string);
    }
}
