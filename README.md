# staging-servers-manager
検証環境マネージャ（ロリポップレンタルサーバ対応）

## 概要
さくらのクラウド上の検証環境（複数サーバ + Coder サーバ）をプロジェクト単位で管理する管理コンソールです。 
Google Cloud Identity（Free）のSSOでログインし、各プロジェクトのサーバ起動/停止と Coder へのオンデマンド起動を行います。

## 必要要件
- PHP 8.1 以上
- MySQL（ロリポップのMySQL）
- さくらのクラウド API のアクセストークン/シークレット
- Google Cloud Identity（Free）に登録済みの Google アカウント

## ロリポップ側のセットアップ
1. **SSL 証明書の設定**
	- ロリポップの管理画面で独自SSLを有効化してください。

2. **データベース作成**
	- ロリポップの MySQL を作成し、以下のテーブルを用意します。

```sql
CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  sakura_project_id VARCHAR(64) NOT NULL,
  sakura_api_token VARCHAR(255) NOT NULL,
  sakura_api_secret VARCHAR(255) NOT NULL,
  sakura_zone VARCHAR(64) NOT NULL DEFAULT 'is1a',
  coder_fqdn VARCHAR(255) NOT NULL,
  coder_health_url VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

3. **ドキュメントルート**
	- FTP の配置先を Web 公開ディレクトリ（例: public_html）に設定してください。
	- `.htaccess` が有効なプランを利用してください。

## Google Cloud Identity（SSO）設定
1. Google Cloud Console で OAuth クライアントを作成します（タイプ: ウェブアプリ）。
2. リダイレクト URI に `https://あなたのドメイン/oauth2/callback` を登録します。
3. 発行された **クライアントID / クライアントシークレット** を GitHub Secrets に登録します。

## GitHub Actions / Secrets 設定
本リポジトリでは **GitHub Actions** の FTP Deploy でロリポップへ自動転送します。 
FTP パスワードは GitHub Secrets（または Codespaces Secrets）で管理してください。

### 必須 Secrets
| 名前 | 例 | 説明 |
|---|---|---|
| FTP_SERVER | ftp.lolipop.jp | FTP サーバ |
| FTP_USERNAME | lolipop-user | FTP ユーザ名 |
| FTP_PASSWORD | ******** | FTP パスワード |
| FTP_REMOTE_DIR | /public_html/ | アップロード先ディレクトリ |
| APP_URL | https://example.com | アプリの公開URL |
| DB_DSN | mysql:host=localhost;dbname=xxx;charset=utf8mb4 | PDO DSN |
| DB_USER | db_user | DB ユーザ |
| DB_PASS | ******** | DB パスワード |
| GOOGLE_CLIENT_ID | xxxx.apps.googleusercontent.com | Google OAuth クライアントID |
| GOOGLE_CLIENT_SECRET | ******** | Google OAuth クライアントシークレット |
| GOOGLE_REDIRECT_URI | https://example.com/oauth2/callback | リダイレクトURI |
| ALLOWED_DOMAINS | example.com,example.jp | 許可ドメイン（任意） |
| ADMIN_EMAILS | admin@example.com | 管理者メール（任意） |
| SESSION_NAME | staging_servers_manager | セッション名（任意） |

### Secrets の取り扱い
- **FTP のパスワードはリポジトリに絶対に含めない**でください。
- GitHub Actions または Codespaces Secrets にのみ登録してください。

## さくらのクラウド API 設定
1. プロジェクトごとに **アクセストークン / シークレット** を発行します。
2. 管理コンソール（/admin/projects）から以下を登録します。
	- プロジェクトID
	- APIキー（Access Token）
	- APIシークレット（Access Token Secret）
	- ゾーン（例: is1a）
	- Coder サーバ FQDN

## Coder のオンデマンド起動
ユーザが `/coder/{projectId}` にアクセスすると、
1. さくらのクラウド API でサーバ起動を指示
2. ブラウザ側の JavaScript が Coder のヘルスチェックをポーリング
3. 起動確認後に Coder へリダイレクト

PHP の実行時間制限に配慮し、**サーバ起動待ちはブラウザ側で実施**します。

## デプロイ
main ブランチへ push すると GitHub Actions が動作し、
`SamKirkland/FTP-Deploy-Action` でロリポップへ自動転送されます。

## ローカル開発
1. `config/env.example.php` を参考に `config/env.php` を作成
2. PHP ビルトインサーバで起動
	- `php -S localhost:8000`

## 注意事項
- Coder のヘルスチェック URL は CORS を許可する必要があります。
- 管理コンソールは admin メールアドレスで制限できます（任意）。
- サーバ起動 API の失敗は UI に反映されないため、運用ではログを確認してください。
