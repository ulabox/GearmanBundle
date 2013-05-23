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

For a bundle located at src/Acme/HelloBundle, the worker classes by default should live inside src/Acme/HelloBundle/Gearman/Worker. We can create a simple worker as shown below:

```
<?php

namespace Acme\HelloBundle\Gearman\Worker;

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
$worker = $gearmanManager->getWorker('AcmeHelloBundle:AcmeWorker');

// now you should tell the client that worker must be run
$client->setWorker($worker);

// and finally do the job
$client->doNormalJob('hello_world', json_encode(array('foo' => 'bar')));

// do the job in backgroud
//$client->doBackgroundJob('hello_world', json_encode(array('foo' => 'bar')));
```

To view the result open a console and run the command:

```
$ php app/console gearman:worker:execute --worker=AcmeHelloBundle:AcmeWorker
```
Then run the code above.

##### By the command line:

Open the first console and run:

```
$ php app/console gearman:worker:execute --worker=AcmeHelloBundle:AcmeWorker
```

Now open another console and run:

```
$ php app/console gearman:client:execute --client=UlaboxGearmanBundle:GearmanClient:hello_world --worker=AcmeHelloBundle:AcmeWorker --params="{\"foo\": \"bar\" }"
```

The commands come with a few options, you can see more details in the commands section.

Annotations
------------------

##### Worker annotations
- servers:    Array containing servers
- iterations: The number of executions before job dies

```
<?php

namespace Acme\HelloBundle\Gearman\Worker;

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

namespace Acme\HelloBundle\Gearman\Client;

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

namespace Acme\HelloBundle\Gearman\Worker;

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

For a bundle located at src/Acme/HelloBundle, the client classes by default should live inside src/Acme/HelloBundle/Gearman/Client. The client class by default is associated with the Worker class with the same name, for example, the AcmeClient will be associated with AcmeWorker:

```
<?php

namespace Acme\HelloBundle\Gearman\Client;

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
$client = $gearmanManager->getClient('AcmeHelloBundle:AcmeClient');

// and finally do the job
$client->doNormalJob('hello_world', json_encode(array('foo' => 'bar')));

// do the job in backgroud
//$client->doBackgroundJob('hello_world', json_encode(array('foo' => 'bar')));
```

To view the result open a console and run the command:

```
$ php app/console gearman:worker:execute --worker=AcmeHelloBundle:AcmeWorker
```
Then run the code above.

##### By the command line:

Open the first console and run:

```
$ php app/console gearman:worker:execute --worker=AcmeHelloBundle:AcmeWorker
```

Now open another console and run:

```
$ php app/console gearman:client:execute --client=AcmeHelloBundle:AcmeClient:hello_world  --params="{\"foo\": \"bar\" }"
```

Run Tasks
------------------
Add a task to be run in parallel with other tasks is very simple. Adding to the previous worker a new job:

```
<?php

namespace Acme\HelloBundle\Gearman\Worker;

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
$client = $gearmanManager->getClient('AcmeHelloBundle:AcmeClient');

// add multiple background tasks
$client->addTaskBackground('hello_world', json_encode(array('foo' => 'bar')));
$client->addTaskBackground('send_newsletter', json_encode(array('Ivan' => 'ivannis.suarez@gmail.com', 'Ulabox' => 'info@ulabox.com')));

// run tasks
$client->runTasks();
```

To view the result open a console and run the command:

```
$ php app/console gearman:worker:execute --worker=AcmeHelloBundle:AcmeWorker
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
