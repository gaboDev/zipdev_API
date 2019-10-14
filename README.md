# ZipDev Code Challenge: Phone Book API

This project was developed using PHP to accomplish the code challenge by ZipDev.

The architecture of the app was designed to add seamlessly new features based on one of the SOLID principles. Open Closed Principle: Open to extension - Closed to modification.

I decided to create a mini framework to take advantage of the perks this type of application gives us.

## Solution

The following list describes the architecture of the framework along with their features
 
- `public/`
    - `index.php:` This file is in charge of bootstrapping the application.

- `resources/`
    - This folder contains the database model and the SQL dump.

- `src/`

- `Classes/`
    - This folder contains a utility class and the classes needed for the creatins of the Response object

- `Config/`
    - `Bootloader.php`: This class builds the main framework instance.
    
- `Controllers/`
    - This folder contains the classes in charge of handling the HTTP requests

- `Database/`

- `QueryBuilder/`
This is the query builder of the application, this builds SQL query using objects
    - `BaseModel.php`
        - This is an abstract class that implements the featured for the application ORM.
    - `DatabaseConnection.php`
        - This file is the connection to the database
    - `Utils.php`
        - This is a utils class for the ORM

- `Models/`
    - App Models

- Routing
- API endpoints

The main features of this framework are designed to fulfill the code challenge requirements  

   

## API Reference
https://gabodev.github.io
 

## Live DEMO
http://74.208.22.233/api/v1/persons

