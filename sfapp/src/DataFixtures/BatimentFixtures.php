<?php

/**
 * Class BatimentFixtures
 *
 * This class is a data fixture for loading Batiment entities into the database.
 * It extends the base Fixture class provided by Doctrine.
 *
 * This fixture is responsible for creating and persisting predefined Batiment entities
 * to be used in the application for development and testing purposes.
 *
 * The load method initializes Batiment entities with specific properties and
 * stores them in the database using the Doctrine ObjectManager.
 */

namespace App\DataFixtures;

use /**
 * Class Batiment
 *
 * Represents a Batiment entity in the application.
 *
 * This class is a part of the Symfony v6.4.17 application.
 *
 * It is used to define the structure and properties of a Batiment object
 * as well as any related behaviors and associations with other entities.
 *
 * This entity is typically mapped to a database table using Doctrine ORM.
 */
    App\Entity\Batiment;
use /**
 * Class Fixture
 *
 * This class is an abstract base class for all Doctrine fixture classes.
 * It is used for loading data fixtures into a database.
 *
 * Fixtures are used to populate a database with test or initial data
 * to simplify application development and testing.
 *
 * Extend this class to implement specific fixture data that needs to be loaded.
 *
 * This class is part of the Doctrine Fixtures Bundle for Symfony.
 */
    Doctrine\Bundle\FixturesBundle\Fixture;
use /**
 * Interface ObjectManager
 *
 * The ObjectManager interface is used in Doctrine for managing
 * the persistence layer. It serves as the primary point of interaction
 * with the Doctrine ORM or ODM for persisting, retrieving, and removing objects.
 */
    Doctrine\Persistence\ObjectManager;

/**
 *
 */
class BatimentFixtures extends Fixture
{

    public const BATIMENT_C = 'Batiment C';
    public const BATIMENT_D = 'Batiment D';

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $C=$this->make_batiment("C",3);
        $D=$this->make_batiment("D",4);

        $manager->persist($C);
        $manager->persist($D);

        $this->addReference(self::BATIMENT_C, $C);
        $this->addReference(self::BATIMENT_D, $D);

        $manager->flush();
    }


    public function make_batiment(string $char, int $e) : Batiment
    {
        $A = new Batiment();
        $A->setNom("Batiment ".$char);
        $A->setNbEtages($e);
        $A->setAdresse("15, rue vaux de foletier 17000 La Rochelle");
        return $A;
    }
}
