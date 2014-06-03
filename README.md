# GearmanBundle

The bundle provides an interface between symfony2 projects and Gearman Job Server.

Quick installation
------------------

Require the bundle in your composer.json file:

```
{
    "require": {
        "ulabox/gearman-bundle": "*",
    }
}
```

Run composer update command:

```
$ composer update ulabox/gearman-bundle
```

Now add the Bundle to your Kernel:

```
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Ulabox\Bundle\GearmanBundle\UlaboxGearmanBundle(),
        // ...
    );
}
```

[Gearman](http://gearman.org) dependency
-----------------------------------

To install Gearman Job Server for **Debain/Ubuntu Package** using the following commands:

```
$ sudo apt-get install gearman-job-server
```

Once the job server has been installed, can be started by running:

```
$ service gearman-job-server start
```

**Install the Gearman driver**

To install **Gearman driver** using the following commands:

```
$ pecl install channel://pecl.php.net/gearman-0.8.3
```

Now we just need to enable the module. Edit your /etc/php5/conf.d/gearman.ini  file and add the line:

```
extension=gearman.so
```

Configuration
------------------

By default, the bundle doesn't need any configuration, but you can configure it editing your config.yml:

```
ulabox_gearman:
    # Define your own servers.
    # By default is set to localhost 127.0.0.1:4730.
    # If annotations defined, will be overwritten.
    servers:
        localhost:
            host: 127.0.0.1
            port: 4730
        server2:
            host: myotherhost
            port: 4567
    # Define the default method to execute a task.
    # By default is set to doBackgroundJob.
    # Available methods (doBackgroundJob, doHighJob, doHighBackgroundJob, doLowJob, doLowBackgroundJob, doNormalJob,
    # addTask, addTaskBackground, addTaskHigh, addTaskHighBackground, addTaskLow, addTaskLowBackground, runTasks).
    default_method: doLowBackgroundJob

    # Define the default number of executions before job dies.
    # By default is set to 100.
    # If annotations defined, will be overwritten.
    iterations: 150

    # Define your workers location directory.
    # By default is set to Gearman/Worker
    worker_dir: MyWorkerDir

    # Define your clients location directory.
    # By default is set to Gearman/Client
    client_dir: MyClientDir
```

Writing Simple Worker
------------------

For a bundle located at src/Acme/DemoBundle, the worker classes by default should live inside src/Acme/DemoBundle/Gearman/Worker. We can create a simple worker as shown below:

```
<?php

namespace Acme\DemoBundle\Gearman\Worker;

use Ulabox\Bundle\GearmanBundle\Model\ContainerAwareWorker;
use Ulabox\Bundle\GearmanBundle\Annotation\Worker;
use Ulabox\Bundle\GearmanBundle\Annotation\Job;

/**
 * The worker.
 *
 * @Worker()
 */
class AcmeWorker extends ContainerAwareWorker
{
    /**
     * The hello world job.
     *
     * @param \GearmanJob $job The GearmanJob instance
     * @return boolean
     * @Job()
     */
    public function hello_world(\GearmanJob $job)
    {
        echo "Received hello world job: " . $job->handle() . "\n";

        $workload     = $job->workload();
        $workloadSize = $job->workloadSize();

        echo "Workload: $workload ($workloadSize)\n";

        // This status loop is not needed, just to show how work
        for ($i = 0; $i < $workloadSize; $i ++) {
            echo "Sending status: " . $i . "/$workloadSize completed\n";

            $job->sendStatus($i, $workloadSize);
            sleep(1);
        }

        echo "Result: '".$workload."' loaded\n";

        return true;
    }

    /**
     * This job is never call, because is not marked with @Job annotation.
     *
     * @param  \GearmanJob $job The GearmanJob instance
     * @return boolean
     */
    public function never_call_job(\GearmanJob $job)
    {
        ...
    }
}
```

> Note how the the worker class is marked with @Worker() annotation and each method considered as a job  is also marked with the @Job() annotation.

#### Executing the job

Once your job have been written, can be run in two ways:

##### By code:

```
// get the gearman manager
$gearmanManager = $this->container->get('ulabox_gearman.manager');

// get the generic gearman client
$client = $gearmanManager->getClient('UlaboxGearmanBundle:GearmanClient');

// find your worker
$worker = $gearmanManager->getWorker('AcmeDemoBundle:AcmeWorker');

// now you should tell the client that worker must be run
$client->setWorker($worker);

// and finally do the job
$client->doNormalJob('hello_world', json_encode(array('foo' => 'bar')));

// do the job in backgroud
//$client->doBackgroundJob('hello_world', json_encode(array('foo' => 'bar')));
```

To view the result open a console and run the command:

```
$ php app/console gearman:worker:execute --worker=AcmeDemoBundle:AcmeWorker
```
Then run the code above.

##### By the command line:

Open the first console and run:

```
$ php app/console gearman:worker:execute --worker=AcmeDemoBundle:AcmeWorker
```

Now open another console and run:

```
$ php app/console gearman:client:execute --client=UlaboxGearmanBundle:GearmanClient:hello_world --worker=AcmeDemoBundle:AcmeWorker --params="{\"foo\": \"bar\" }"
```

The commands come with a few options, you can see more details in the commands section.

Annotations
------------------

##### Worker annotations
- servers:    Array containing servers
- iterations: The number of executions before job dies

```
<?php

namespace Acme\DemoBundle\Gearman\Worker;

use Ulabox\Bundle\GearmanBundle\Model\ContainerAwareWorker;
use Ulabox\Bundle\GearmanBundle\Annotation\Worker;
use Ulabox\Bundle\GearmanBundle\Annotation\Job;

/**
 * The worker.
 *
 * @Worker(servers={"127.0.0.1:4730"}, iterations=10)
 */
class AcmeWorker extends ContainerAwareWorker
{
    ....
}
```

##### Client annotations
- worker:  The worker name
- servers: Array containing servers

```
<?php

namespace Acme\DemoBundle\Gearman\Client;

use Ulabox\Bundle\GearmanBundle\Annotation\Client;
use Ulabox\Bundle\GearmanBundle\Model\Client as BaseClient;

/**
 * The client
 *
 * @Client(worker="MyWorker", servers={"127.0.0.1:4730"})
 */
class AcmeClient extends BaseClient
    ....
}
```

##### Job annotations
- name:  The job name

```
<?php

namespace Acme\DemoBundle\Gearman\Worker;

use Ulabox\Bundle\GearmanBundle\Model\ContainerAwareWorker;
use Ulabox\Bundle\GearmanBundle\Annotation\Worker;
use Ulabox\Bundle\GearmanBundle\Annotation\Job;

/**
 * The worker.
 *
 * @Worker(servers={"127.0.0.1:4730"}, iterations=10)
 */
class AcmeWorker extends ContainerAwareWorker
{
    /**
     * The hello world job.
     *
     * @param \GearmanJob $job The GearmanJob instance
     * @return boolean
     *
     * @Job(name="acme_hello_world")
     */
    public function hello_world(\GearmanJob $job)
    {
        ....
    }
}
```

Writing Simple Client
------------------

For a bundle located at src/Acme/DemoBundle, the client classes by default should live inside src/Acme/DemoBundle/Gearman/Client. The client class by default is associated with the Worker class with the same name, for example, the AcmeClient will be associated with AcmeWorker:

```
<?php

namespace Acme\DemoBundle\Gearman\Client;

use Ulabox\Bundle\GearmanBundle\Annotation\Client;
use Ulabox\Bundle\GearmanBundle\Model\Client as BaseClient;

/**
 * The client
 *
 * @Client()
 */
class AcmeClient extends BaseClient
{
    public function hello_world_status($status)
    {
        print_r("AcmeClient::Status ".$status."\n");
    }

    public function hello_world_data($task)
    {
        print_r("AcmeClient::Data: ".$task->data()."\n");
    }

    public function hello_world_fail($task)
    {
        print_r("AcmeClient::Failed: ".$task->jobHandle()."\n");
    }

    public function hello_world_success($result)
    {
        print_r("AcmeClient::Success: ".$result);
    }
}
```
> Note how the the client class has a callback methods to be notified when the worker do the job, but it only makes sense when the job is executing with doNormalJob method.

#### Executing the job through client

Once your job have been written, can be run in two ways:

##### By code:

```
// get the gearman manager
$gearmanManager = $this->container->get('ulabox_gearman.manager');

// get the acme client
$client = $gearmanManager->getClient('AcmeDemoBundle:AcmeClient');

// and finally do the job
$client->doNormalJob('hello_world', json_encode(array('foo' => 'bar')));

// do the job in backgroud
//$client->doBackgroundJob('hello_world', json_encode(array('foo' => 'bar')));
```

To view the result open a console and run the command:

```
$ php app/console gearman:worker:execute --worker=AcmeDemoBundle:AcmeWorker
```
Then run the code above.

##### By the command line:

Open the first console and run:

```
$ php app/console gearman:worker:execute --worker=AcmeDemoBundle:AcmeWorker
```

Now open another console and run:

```
$ php app/console gearman:client:execute --client=AcmeDemoBundle:AcmeClient:hello_world  --params="{\"foo\": \"bar\" }"
```

Run Tasks
------------------
Add a task to be run in parallel with other tasks is very simple. Adding to the previous worker a new job:

```
<?php

namespace Acme\DemoBundle\Gearman\Worker;

use Ulabox\Bundle\GearmanBundle\Model\ContainerAwareWorker;
use Ulabox\Bundle\GearmanBundle\Annotation\Worker;
use Ulabox\Bundle\GearmanBundle\Annotation\Job;

/**
 * The worker.
 *
 * @Worker(servers={"127.0.0.1:4730"}, iterations=10)
 */
class AcmeWorker extends ContainerAwareWorker
{
    /**
     * Execute a job.
     *
     * @param \GearmanJob $job The GearmanJob instance
     *
     * @return boolean
     *
     * @Job()
     */
    public function hello_world(\GearmanJob $job)
    {
        echo "Received hello world job: " . $job->handle() . "\n";

        $workload     = $job->workload();
        $workloadSize = $job->workloadSize();

        echo "Workload: $workload ($workloadSize)\n";

        // This status loop is not needed, just to show how work
        for ($i = 0; $i < $workloadSize; $i ++) {
            echo "Sending status: " . $i . "/$workloadSize completed\n";

            $job->sendStatus($i, $workloadSize);
            sleep(1);
        }

        echo "Result: '".$workload."' loaded\n";

        return true;
    }

    /**
     * Execute a job.
     *
     * @param \GearmanJob $job The GearmanJob instance
     *
     * @return boolean
     *
     * @Job()
     */
    public function send_newsletter(\GearmanJob $job)
    {
        $users = json_decode($job->workload(), true);

        foreach ($users as $name => $email) {
            echo "Be sent an email to $name\n";
            // send the email
            echo "The email have been send to $email\n\n";
            sleep(1);
        }

        echo count($users)." mails have been sent \n";

        return true;
    }
}
```

Once your job have been written, can be run as show below:

```
// get the gearman manager
$gearmanManager = $this->container->get('ulabox_gearman.manager');

// get the acme client
$client = $gearmanManager->getClient('AcmeDemoBundle:AcmeClient');

// add multiple background tasks
$client->addTaskBackground('hello_world', json_encode(array('foo' => 'bar')));
$client->addTaskBackground('send_newsletter', json_encode(array('Ivan' => 'ivannis.suarez@gmail.com', 'Ulabox' => 'info@ulabox.com')));

// run tasks
$client->runTasks();
```

To view the result open a console and run the command:

```
$ php app/console gearman:worker:execute --worker=AcmeDemoBundle:AcmeWorker
```
Then run the code above.

Commands
------------------

Show all workers registered:

```
$ php app/console gearman:worker:list
```

Execute a job:

```
$ php app/console gearman:client:execute --help
```

Execute a worker:

```
$ php app/console gearman:worker:execute --help
```

Asynchronous Events
------------------

Exploiting the potential of Gearman Job Server, we have introduced a new EventDispatcherAsync that replaces the default symfony EventDispatcher, the new dispatcher has a new method called dispatchAsync that sends the event to the gearman queue, gearman processes it and sends it back to the php application, which finally reconstructs the event and run it asynchronously.

#### Configuration

To use asynchronous events yo can optionally send events via request, if so edit the routing.yml file of the gearman bundle.

```
# app/config/routing.yml
gearman_bundle:
    resource: @UlaboxGearmanBundle/Resources/config/routing.yml
    prefix:   /_gearman
```

For request processing the event and send it back to the app. This worker needs to generate a url from the command line, and this requires us setup the request context globally.

```
# app/config/parameters.yml
parameters:
    router.request_context.host: example.org
    router.request_context.scheme: https
    router.request_context.base_url: my/path
```

And add the route in the access control from your security.yml file.

```
# app/config/security.yml
security:
    # ...
    access_control:
        - { path: ^/_gearman*, roles: IS_AUTHENTICATED_ANONYMOUSLY, ips: [127.0.0.1, ::1] }
```

Enable the asynchronous event dispatcher in your config.yml.
```
# app/config/config.yml
ulabox_gearman:
    enable_asynchronous_event_dispatcher: true
```

Or you can enable the cli async event dispatcher instead.
```
# app/config/config.yml
ulabox_gearman:
    enable_asynchronous_cli_event_dispatcher: true
```

Now that everything is configured the following step is run the EventWorker in the command line

```
$ php app/console gearman:worker:execute --worker=UlaboxGearmanBundle:EventWorker
```

If you decided to use the cli EventWorker use this line instead :

```
$ php app/console gearman:worker:execute --worker=UlaboxGearmanBundle:CliEventWorker
```

> The EventWorker should always be running, so we recommend using a tool such as the [supervisor](http://supervisord.org/) to ensure that the worker is always running.

#### Writing an async Event

An async Event is a simple event with the only difference that must implement the interface Ulabox\Bundle\GearmanBundle\Dispatcher\AsyncEventInterface. Here we show a small example:


```
<?php

namespace Acme\DemoBundle\Event;

use Ulabox\Bundle\GearmanBundle\Dispatcher\AsyncEventInterface;
use Symfony\Component\EventDispatcher\Event;
use Acme\DemoBundle\Entity\User;

/**
 * The custom async event.
 */
class FooEvent extends Event implements AsyncEventInterface
{
    /**
     * The user entity
     *
     * @var User
     */
    protected $user;

    /**
     * Construstor.
     *
     * @param User $user The user instance
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return array(
            'user' => $this->user
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setArguments(array $args = array())
    {
        $this->user = $args['user'];
    }
}
```

Note that the class implements the two AsyncEventInterface methods (getArguments, setArguments), these are used internally to reconstruct the event.

##### Create an Event Listener

```
<?php

namespace Acme\DemoBundle\EventListener;

use Acme\DemoBundle\Event\FooEvent;

/**
 * Foo listener
 */
class FooListener
{
    /**
     * Listener on foo event
     *
     * @param FooEvent $event The event
     */
    public function onFoo(FooEvent $event)
    {
        $user = $event->getUser();
        // do something
    }
}
```

Now that the class is created, you just need to register it as a service:
```
# app/config/config.yml
services:
    acme.listener.foo_event:
        class: Acme\DemoBundle\EventListener\FooListener
        tags:
            - { name: kernel.event_listener, event: foo.event, method: onFoo }
```

##### Dispatch the Event

The dispatchAsync() method begins the process and notifies asynchronously all listeners of the given event
```
<?php

namespace Acme\DemoBundle\Controller;

use Acme\DemoBundle\Event\FooEvent;

/**
 * Demo controller
 */
class DemoController
{
    public function someAction()
    {
        ...

        $user = $this->get('security.context')->getToken()->getUser();
        $eventDispatcher = $this->get('event_dispatcher');

        // dispatch the event
        $eventDispatcher->dispatchAsync('foo.event', new FooEvent($user));

        ...
    }
}
```