<?php

namespace Gedmo\DemoBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Gedmo\DemoBundle\Entity\Category;
use Gedmo\DemoBundle\Entity\CategoryTranslation;
use Gedmo\DemoBundle\Entity\Language;

class TestDataReloadCommand extends DoctrineCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('demo:reload')
            ->setDescription('Reloads test data.')
            ->setDefinition(array(
                new InputOption(
                    'em', null, InputOption::VALUE_OPTIONAL,
                    'Set the default database collation.',
                    'default'
                )
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emName = $input->getOption('em');
        $em = $this->getEntityManager($emName);

        // deletions
        $conn = $em->getConnection();
        foreach (array('demo_languages', 'demo_categories', 'demo_category_translations') as $tbl) {
            $statement = $conn->prepare($conn->getDatabasePlatform()->getTruncateTableSQL($tbl, true));
            $statement->execute();
        }

        $lang0 = new Language;
        $lang0->setTitle('En');

        $lang1 = new Language;
        $lang1->setTitle('De');

        $em->persist($lang0);
        $em->persist($lang1);

        $translatable = $this->getContainer()->get(
            'gedmo.listener.translatable'
        );
        $translatable->setTranslatableLocale('en');

        $food = new Category;
        $food->setTitle('Food');
        $food->setDescription('Food');
        $food->addTranslation(new CategoryTranslation('de', 'title', 'Lebensmittel'));
        $food->addTranslation(new CategoryTranslation('de', 'description', 'Lebensmittel'));

        $em->persist($food);
        $cars = new Category;
        $cars->setTitle('Cars');
        $cars->setDescription('Cars');
        $cars->addTranslation(new CategoryTranslation('de', 'title', 'Autos'));
        $cars->addTranslation(new CategoryTranslation('de', 'description', 'Autos'));

        $em->persist($cars);

        $sportCars = new Category;
        $sportCars->setTitle('Sport Cars');
        $sportCars->setDescription('Cars->Sport Cars');
        $sportCars->setParent($cars);

        $em->persist($sportCars);

        $electricCars = new Category;
        $electricCars->setTitle('Electric Cars');
        $electricCars->setDescription('Cars->Electric Cars');
        $electricCars->setParent($cars);

        $em->persist($electricCars);

        $fruits = new Category;
        $fruits->setTitle('Fruits');
        $fruits->setDescription('Food->Fruits');
        $fruits->setParent($food);

        $em->persist($fruits);

        $milk = new Category;
        $milk->setTitle('Milk');
        $milk->setDescription('Food->Milk');
        $milk->setParent($food);

        $em->persist($milk);

        $vegetables = new Category;
        $vegetables->setTitle('Vegetables');
        $vegetables->setDescription('Food->Vegetables');
        $vegetables->setParent($food);
        $vegetables->addTranslation(new CategoryTranslation('de', 'title', 'Gemüse'));
        $vegetables->addTranslation(new CategoryTranslation('de', 'description', 'Lebensmittel->Gemüse'));

        $em->persist($vegetables);

        $onions = new Category;
        $onions->setTitle('Onions');
        $onions->setDescription('Food->Vegetables->Onions');
        $onions->setParent($vegetables);

        $em->persist($onions);

        $carrots = new Category;
        $carrots->setTitle('Carrots');
        $carrots->setDescription('Food->Vegetables->Carrots');
        $carrots->setParent($vegetables);
        $carrots->addTranslation(new CategoryTranslation('de', 'title', 'Möhren'));
        $carrots->addTranslation(new CategoryTranslation('de', 'description', 'Lebensmittel->Gemüse->Möhren'));

        $em->persist($carrots);

        $cabbages = new Category;
        $cabbages->setTitle('Cabbages');
        $cabbages->setDescription('Food->Vegetables->Cabbages');
        $cabbages->setParent($vegetables);

        $em->persist($cabbages);

        $potatoes = new Category;
        $potatoes->setTitle('Potatoes');
        $potatoes->setDescription('Food->Vegetables->Potatoes');
        $potatoes->setParent($vegetables);

        $em->persist($potatoes);
        $em->flush();

        $output->writeLn('Reload Done..');
    }
}
