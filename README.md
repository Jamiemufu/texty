# texty
Codevate Techical Test

This project was built on Symfony 4.4.

# Setup

1) Clone repository.
2) Edit .env file (.env.local/.env).
3) Inside the .env set ADMIN_EMAIL, ADMIN_PASSWORD - This is used for DataFixtures and will create a super admin user of this email and password.
4) Set your twilio credentials - Sid, Token and your twilio phone number.
5) Set your twilio callback uri - This needs to be a public accessable domain.
    Twilio sends status updates via a POST request callback, change the uri here, and it will all work fine.
6) You need uncomment your transport for RabbidMQ queues - uncomment MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/ and change appropriately.
7) Change DATABASE_URL=mysql://root:@127.0.0.1:3306/table?serverVersion=5.7 to your database user, password and table you want to use.
8) Create the table, then run migrations via => php bin/console doctrine:migrations:migrate.
9) If the database details are correct migrations should be run and the database set up, now we can run fixutres to create our admin user.
10) php bin/console doctrine:fixtures:load
11) To consume the queue use command php bin/console messenger:consume async.
12) On production you would install addition workers and packages - but basically you can run this and it will consume messages in the queue.
13) It's using the built-in Symfony messaging service, you can also cron this.
14) To get it up and running quickly just use => symfony server:start

Front end is very basic, but I have used webpack, to demonstrate I can set it up and use it, also have used scss. These have already been compiled in public/.

# General use

You can now immedetiatly login as admin using credentials supplied in .env.
You can send a message, view sent messages and as super admin find another link that shows you ALL messages sent. Ordered by newest first.
You can register a new user and that user has appropriate roles, where they can't see anyone else's sent messages but their own.
You can login as a neewly registered user and send a message.
Phone numbers are fully validated from misd-service-development/phone-number-bundle.

# Tasks

Full auth system implemented.
All forms are built with Symfony forms or generated as part of auth, with validation.
Phone numbers are appropriatly validated and stored.
Once a sms is submitted, it saves to database, then gets added to RabbitMQ queue. Once consumed (using the Twilio sdk) the sms is sent.
Callback status and generated SMS ID are then updated in the database, if successfull callback is made.


