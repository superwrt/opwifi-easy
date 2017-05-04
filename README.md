
##OpWiFi设备管理平台（Easy版本）

OpWiFi是由Terra针对[SuperWRT](http://superwrt.com)系统开发的设备管理平台。

使用PHP编写，采用了Laravel框架。

OpWiFi是SuperWRT项目的一部分，官方网站是：[http://superwrt.com](http://superwrt.com)

目前提供了一个低配置的虚拟主机(600M空间)作为演示用系统，地址为：[http://demo1.opwifi.com](http://demo1.opwifi.com) 。登录的用户名及密码都为：test。

由于Terra对于PHP开发，也是边写边学，有问题的地方，欢迎指正。

##OpWiFi功能

OpWiFi Easy版本使用HTTP协议与设备交互，有数字签名验证，可选对内容进行加密。支持以功能：

* 管理设备：
    * 查看设备状态
    * 给设备下发指令
    * 升级设备固件版本
* Web Portal：
    * 可以下发Web Portal配置
    * 将不同配置绑定到不同设备
    * 自身集成用户名、密码登录方式
    * 通过与SuperWRT设备配合：
        * 支持域名和IP白名单
        * 可完成用户限速、限流量、限时长等功能

#有问题反馈

在使用中有任何问题，欢迎反馈给我，可以用以下联系方式跟我交流

* 邮件：[terra@superwrt.com](terra@superwrt.com)
* QQ: 1461420057
* 微信公众号: [Super-WRT]


##贡献代码

欢迎开发人员加入我们，一起贡献代码，以帮助OpWiFi项目更好的成长。

##捐助开发者

虽然OpWiFi是`免费`的，您可以无偿使用，但也希望您可以捐助我们，以支持我们将该项目发展的更好。

##感激

感谢以下的项目,排名不分先后

* [php](http://www.php.net)
* [mysql](http://www.mysql.com)
* [laravel](http://laravel.com)
* [bootstrap](http://getbootstrap.com)
* [jquery](http://jquery.com)
* [bootstrap-table](http://bootstrap-table.wenzhixin.net.cn)
* [jstree](http://www.jstree.com)

##使用许可

OpWiFi Easy版本为完全开源，不限止任何商业使用行为，使用Apache v2许可。

特别条款：您可以对OpWiFi项目进行任何更改，但以下内容必须保留：

* 必须在用户管理界面有OpWiFi项目字样，及指向opwifi.com的链接。
* 在用户管理界面的官网菜单内容必须原样保留。
