关于 Controller、Service、Repository、Model的职责其实并没有一个完全正确的答案，
Repository和Service完全可以不引入，但是考虑到软件的可维护性和扩展性还是需要尽量符合面向对象的SOLID原则来进行设计，所以把这两部分引入到了脚手架中。
下面贴一个stackoverflow上对这几部分职责解释的比较清楚的答案


Controllers: What is the responsibility of Controllers? Sure, you can put all your logic in a controller, but is that the controller's responsibility? I don't think so.

For me, the controller must receive a request and return data and this is not the place to put validations, call db methods, etc..

Models: Is this a good place to add logic like sending an welcome email when a user registers or update the vote count of a post? What if you need to send the same email from another place in your code? Do you create a static method? What if that emails needs information from another model?

I think the model should represent an entity. With Laravel, I only use the model class to add things like  fillable, guarded, table and the relations (this is because I use the Repository Pattern, otherwise the model would also have the save, update, find, etc methods).

Repositories (Repository Pattern): At the beginning I was very confused by this. And, like you, I thought "well, I use MySQL and thats that.".

However, I have balanced the pros vs cons of using the Repository Pattern and now I use it. I think that now, at this very moment, I will only need to use MySQL. But, if three years from now I need to change to something like MongoDB most of the work is done. All at the expense of one extra interface and a $app->bind(«interface», «repository»).

Events (Observer Pattern): Events are useful for things that can be thrown at any class any given time. Think, for instance, of sending notifications to a user. When you need, you fire the event to send a notification at any class of your application. Then, you can have a class like UserNotificationEvents that handles all of your fired events for user notifications.

Services: Until now, you have the choice to add logic to controllers or models. For me, it makes all sense to add the logic within Services. Let's face it, Services is a fancy name for classes. And you can have as many classes as it makes sense to you within your aplication.

Take this example: A short while ago, I developed something like the Google Forms. I started with a CustomFormService and ended up with CustomFormService, CustomFormRender, CustomFieldService, CustomFieldRender, CustomAnswerService and CustomAnswerRender. Why? Because it made sense to me. If you work with a team, you should put your logic where it makes sense to the team.

The advantage of using Services vs Controllers / Models is that you are not constrained by a single Controller or a single Model. You can create as many services as needed based on the design and needs of your application. Add to that the advantage of calling a Service within any class of your application.

#### 示例

假设要查询所有管理员用户信息

- 创建UserRepositoryInterface

```
php artisan make contract UserRepositoryInterface --sub-path=Repositories
```
UserRepositoryInterface的代码如下:

```
<?php
namespace App\Contracts\Repositories;

interface UserRepositoryInterface extends RepositoryInterface
{
    // 示例代码, 请删除
    const TYPE_NORMAL = 1;
    const TYPE_ADMIN = 2;
    
    public function admins();
}
```
RepositoryInterface里对常用的数据查询和更新方法做了定义，UserRepositoryInterface中只需要添加需要单独定义的方法声明。


- 创建UserRepository

```
php artisan make:repository UserRepository  --sub-path=Eloquent // sub-path的默认值是Eloquent
```

因为Repository目前对接的都是Model，所以Repository会默认创建到./app/Repositories/Eloquent这个目录，假设以后User切换到MongoDB中
那么我们可以在MongoDB目录中去定义Repository，UserRepository的代码如下:

```
<?php
namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;

class UserRepository extends Repository implements UserRepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() : string
    {
        return \App\Models\User::class;
    }
    
    /**
     * Return all admin users
     *
     * @return Collection
     */
    public function admins()
    {
        // 也可以不使用Repository的方法，在User Model 中定义查询作用域scopeAdmins, 然后return $this->model->admins()->get();
        return $this->model->findAllBy('type', UserRepositoryInterface::TYPE_ADMIN);
    }
}
```
父类Repository中会实现RepositoryInterface中定义的通用方法，并且声明了抽象方法`model`要求子类必须说明对接的Model。

- 将Repository接口约定和实现绑定到服务容器中

在 ./app/Providers/RepositoryServiceProvider中注册接口和实现
```
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
    }
}
```

- 创建UserService

```
php artisan make:service UserService;
```

```
namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;

class TestService 
{
    /**
     * @var UserRepositoryInterface
     */
    public $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * 执行复杂的操作
     * @param $something
     * @return mixed
     */
    public function getAllAdminUsers()
    {
        // TODO: 前置操作
        $admins = $this->userRepo->admins();
        // TODO: 后置操作
        
        return ...
    }


}
```

- 扩展

假设一段时间以后，需要将UserRepository对接到MongoDB数据库，那么我们需要在
1. 在 ./app/Repsitories/MongoDB目录中定义一个父类Repository， 它需要实现./app/Contracts/Repository/RepositoryInterface中的所有接口。
2. 在 ./app/Repsitories/MongoDB目录中定义UserMogoRepository继承Repository并实现UserRepositoryInterface中声明的方法。
3. 在RepositoryServiceProvider中将UserRepositoryInterface切换到新的实现。

```
    public function register()
    {
        $this->app->singleton(UserRepositoryInterface::class, UserMongoRepository::class);
    }
```

这样使用UserRepository的上层应用程序完全不需要修改代码就能完成底层数据的切换。