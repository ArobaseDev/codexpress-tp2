<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\View;
use App\Entity\Offer;
use App\Entity\Network;
use App\Entity\Category;
use App\Entity\Subscription;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $slug = null;
    private $hash = null;

    public function __construct(
        private SluggerInterface $slugger,
        private UserPasswordHasherInterface $hasher
    ) {
        $this->slug = $slugger;
        $this->hash = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création de catégories
        $categories = [
            'HTML' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/html5/html5-plain.svg',
            'CSS' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/css3/css3-plain.svg',
            'JavaScript' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/javascript/javascript-plain.svg',
            'PHP' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-plain.svg',
            'SQL' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/postgresql/postgresql-plain.svg',
            'JSON' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/json/json-plain.svg',
            'Python' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/python/python-plain.svg',
            'Ruby' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/ruby/ruby-plain.svg',
            'C++' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/cplusplus/cplusplus-plain.svg',
            'Go' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/go/go-original-wordmark.svg',
            'bash' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/bash/bash-plain.svg',
            'Markdown' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/markdown/markdown-original.svg',
            'Java' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/java/java-original-wordmark.svg',
        ];

        $networks = ['github', 'twitter', 'linkedin', 'facebook', 'reddit', 'instagram', 'youtube'];

        $categoryArray = []; // Ce tableau nous servira pour conserver les objets Category
      
        // Offers   
        $offer1 = new Offer;
        $offer1->setName('Basic')->setPrice(0.00)->setFeatures($faker->sentence(10));
        $offer2 = new Offer;
        $offer2->setName('Basic')->setPrice(5.90)->setFeatures($faker->sentence(20));
        $offer3 = new Offer;
        $offer3->setName('Pro')->setPrice(9.99)->setFeatures($faker->sentence(20));
        $manager->persist($offer1, $offer2, $offer3);


        foreach ($categories as $title => $icon) {
            $category = new Category(); // Nouvel objet Category
            $category
                ->setTitle($title) // Ajoute le titre
                ->setIcon($icon) // Ajoute l'icone
            ;

            array_push($categoryArray, $category); // Ajout de l'objet
            $manager->persist($category);
        }
        // Admin
        $user =  new User();
        $user
            ->setEmail('hello@codexpress.fr')
            ->setUsername('AroDev')
            ->setPassword($this->hash->hashPassword($user, 'admin'))
            ->setRoles(['ROLE_ADMIN'])
        ;
        $manager->persist($user);

        for ($d = 0; $d < 3; $d++) {
            $network = new Network();
            $network
                ->setName($faker->randomElement($networks))
                ->setUrl('https://' . $network->getName() . '.com/Arodev')
                ->setCreator($user)
            ;
            $manager->persist($network);
        }

        for ($y = 0; $y < 10; $y++) {
            $note = new Note();
            $note
                ->setTitle($faker->sentence())
                ->setSlug($this->slug->slug($note->getTitle()))
                ->setContent($faker->randomHtml())
                ->setPublic($faker->boolean(50))
                ->setCreator($user)
                ->setCategory($faker->randomElement($categoryArray))
            ;
            $manager->persist($note);
        }

        // 10 utilisateurs
        for ($i = 0; $i < 10; $i++) {
            $username = $faker->userName; // Génére un username aléatoire
            $usernameFinal = $this->slug->slug($username); // Username en slug
            $user =  new User();
            $user
                ->setEmail($usernameFinal . '@' . $faker->freeEmailDomain)
                ->setUsername($username)
                ->setPassword($this->hash->hashPassword($user, 'admin'))
                ->setRoles(['ROLE_USER'])
            ;
          
            // Création des offres pour chaque utilisateur
          
                $subscription = new Subscription();
                $subscription
                        ->setCreator($user)
                        ->setStartDate($faker->dateTimeBetween('-1 year', 'now'))
                        ->setEndDate($faker->dateTimeBetween('now', '+1 year'))
                        ->setOffer($faker->randomElement(
                            [
                                $offer1,
                                $offer2,
                                $offer3
                            ]
                        ));
                $manager->persist($subscription);
          
            // Création des réseaux sociaux pour chaque utilisateur
            for ($z = 0; $z < 3; $z++) {
                $network = new Network();
                $network
                    ->setName($faker->randomElement($networks))
                    ->setUrl('https://' . $network->getName() . '.com/' . $usernameFinal)
                    ->setCreator($user)
                ;
                $manager->persist($network);
            }

            $manager->persist($user);

            for ($j = 0; $j < 10; $j++) {
                $note = new Note();
                $note
                    ->setTitle($faker->sentence())
                    ->setSlug($this->slug->slug($note->getTitle()))
                    ->setContent($faker->randomHtml())
                    ->setPublic($faker->boolean(50))
                    ->setPremium($faker->boolean(50))
                    ->setCreator($user)
                    ->setCategory($faker->randomElement($categoryArray))
                ;
                $manager->persist($note);
                // Views
                for ($l = 0; $l < 500; $l++) {
                    $view = new View();
                    $view
                        ->setNote($note)
                        ->setIpAddress($faker->ipv4);
                    $manager->persist($view);
                }
            }
        }

        $manager->flush();
    }
}
