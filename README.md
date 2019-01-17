[![SymfonyInsight](https://insight.symfony.com/projects/144c4c1a-d762-4975-8518-b8bee6a27efa/mini.svg)](https://insight.symfony.com/projects/144c4c1a-d762-4975-8518-b8bee6a27efa)

##Personal Blog - Farem

This project has been realized in the context of OpenClassrooms' "PHP/Symfony Developer path". It is actually the 5th project I have to set up for this path.
The aim is to build it from scratch : this means no use of any framework, CMS or whatever. We are however allowed to integrate external libraries through Composer.

I chose to integrate 3 of those external libraries :
 - Twig : this templating system facilitates the output of HTML pages A LOT. 
 - Datatables : provides several useful features for tables used in the backend (ordering, pagination...)
 - Symfony YAML : I chose to store configuration files in Yaml format, so this is very useful
 - Upload : simple PHP library that handles file uploading
 - CKEditor : to allow admins to write HTML content in the Posts.

I chose 2 different Bootstrap 4 themes, which are 'Donna' for the frontend, and 'Medialoot Bootstrap 4 Backend' for the backend.

To test the website, just clone the zip, import the SQL files located in 'config/database' (one for the structure, the other contains dummy content), install the composer libraries (composer install), and edit both 'config/db.ini' with your database information and 'config/twig.yml' with the base_url of your installation.
