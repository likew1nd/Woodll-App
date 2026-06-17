# WOODLL App

WOODLL App 是基于 ThinkPHP 8 的授权管理与后台控制台系统，适合用于软件授权、卡密发放、用户管理、版本配置和接口验证等场景。项目包含安装向导、后台控制台、用户登录、卡密管理、软件管理、接口配置和运行记录等常用功能，上传后可通过网页向导完成初始化部署。

## 功能介绍

- 安装向导：首次访问自动进入安装流程，检测环境并写入数据库配置。
- 后台登录：支持管理员登录、自动登录选项和暗色登录界面。
- 控制台首页：集中查看系统信息、快捷入口和业务概览。
- 软件管理：维护软件列表、版本信息、更新配置和相关业务参数。
- 卡密管理：支持卡密类型、卡密生成、导出、删除、使用记录查询。
- 用户管理：管理软件用户、登录记录、到期时间和授权状态。
- 接口验证：提供面向客户端的软件授权、卡密验证和业务接口能力。
- 支付配置：预留支付宝、微信支付、码支付等配置入口。
- 反馈与消息：包含用户反馈、消息盒子、回复记录等管理页面。
- 部署友好：内置 ThinkPHP 伪静态参考、`.env.example` 和单文件安装包。

## 项目特点

- 基于 ThinkPHP 8，目录结构清晰。
- 安装完成后同时写入 `.env` 和 `config/database.php`。
- 后台登录页接入 Bing 背景图，并带有鼠标跟随动效。
- 安装包已包含 Composer 依赖，可直接上传部署。
- README 内置界面截图，便于快速了解系统效果。

## 界面预览

### 登录页

![WOODLL 登录页](docs/images/login.png)

### 后台控制中心

![WOODLL 后台控制中心](docs/images/dashboard.png)

## 运行环境

- PHP 8.0+
- MySQL 5.7+ / 8.0+
- Nginx / Apache
- Composer 依赖已随安装包保留

## 安装说明

1. 上传项目到网站目录。
2. 网站运行目录设置为 `public`。
3. Nginx 伪静态设置为 `ThinkPHP`。
4. 首次访问站点会自动跳转到 `/install/index.php`。
5. 按安装向导填写数据库信息并完成安装。

安装完成后，系统会写入 `.env` 和 `config/database.php`，并生成 `public/install/install.lock` 防止重复安装。

## Nginx 伪静态

宝塔面板可直接在网站设置中把伪静态选择为 `ThinkPHP`。也可以参考项目内的 `nginx-thinkphp-rewrite.txt`：

```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```

## 目录说明

- `public/`：网站运行目录。
- `public/install/`：安装向导。
- `config/database.php`：数据库连接配置。
- `.env.example`：环境变量示例。
- `nginx-thinkphp-rewrite.txt`：Nginx ThinkPHP 伪静态参考。

## 单文件安装包

发布包文件：`woodllapp-install.zip`
