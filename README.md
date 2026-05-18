# 🛒 買い物リスト 研修課題

おはようさん、ワシや、ガネーシャや🐘。🎵ガネ・ガネ・ガネーシャモーニング🎵を歌いながら出てきたで。

これはな、Vue 3 × Laravel を使った **TypeScript導入・OpenAPI型自動生成・エラーハンドリング** のハンズオン課題や。お前にはこの「買い物リストアプリ」を、タスクを1つずつクリアしながら鍛え上げていってもらう。スーパーマリオで言うところのワールド1-1からスタートや。

## 🎯 この課題で身につくこと

1. **TypeScript の基礎** — 既存の Vue (JS) コードに型を付けていく感覚
2. **OpenAPI による型の自動生成** — バックエンドと型を常に一致させる仕組み
3. **エラーハンドリングの責務分離** — axios interceptor / composable / UIコンポーネント

> 💡 ワシの教え子のアインシュタインくんも「想像力は知識より大事」言うてたけど、結局のとこ手を動かしてみんと身につかへんからな。読むだけやのうて、実際にコード書いて体験してこそや。

---

## 📚 課題の構成

各タスクは **ブランチ** として用意されてるで。前のタスクの完成形が次のタスクのスタート地点になってる、っちゅう仕掛けや。

| ブランチ | 内容 |
|---|---|
| `task-1` | TypeScript化（手書きで型を当てる）👈 **ここから開始** |
| `task-2` | OpenAPI 型自動生成パイプラインの構築 |
| `task-3` | カラム追加を全レイヤーで貫通 |
| `task-4` | エラーハンドリングの実装 |
| `task-5` | バリデーション + 422表示 |
| `complete` | 全部完成形 |

各タスクの詳細は `docs/task-N.md` を見るんやで。

> 💡 ステージごとにセーブポイントがある、ファミコンのゲームみたいなもんや。途中で詰まったら次のブランチに「正解」があるから、そこから再開できるで。

---

## 🚀 セットアップ

ここからが下ごしらえや。料理と一緒で、ここを丁寧にやらんと後で泣くからな。

### 1. リポジトリを Fork

右上の「Fork」ボタンで自分のアカウントにコピーする。

> 💡 Fork っちゅうのはな、「親のキッチン」を「自分のキッチン(GitHub)」に丸ごとコピーすることや。これでお前の家で好きなだけ料理（コード変更）できるようになる。

### 2. ローカルに clone

```bash
cd ~/Desktop
git clone https://github.com/<あなたのGitHubユーザー名>/shopping-list-training.git
cd shopping-list-training
```

このとき `git remote -v` を打つと `origin`（= 自分の fork）しか登録されてへん。次の手順で **親リポジトリを `upstream` として追加** するで。

### 3. 親リポジトリを `upstream` として登録

```bash
git remote add upstream https://github.com/okumura-env/shopping-list-training.git
git remote -v   # origin と upstream の両方が表示されればOK
```

> 💡 `upstream` は単なる **remote 名の慣習** で、Git の予約語ではないんやで。`origin`（自分の fork）と区別するため「親」の意味で `upstream` と名付けるのが一般的や。
> これで `git fetch upstream` や `git pull upstream task-2` が使えるようになる。

> ⚠️ 初心者あるある：「えっ、`upstream` ってコマンドちゃうの？」って思う人おる。違うで、ただの「ニックネーム」や。`parent` でも `oya` でも動くんやけど、`upstream` がみんなの共通言語っちゅうこっちゃ。

### 4. 環境変数ファイルの準備

```bash
cp .env.example .env
```

> ⚠️ ポートが他のプロジェクトと衝突する場合は `.env` の `APP_PORT` / `VITE_PORT` / `FORWARD_DB_PORT` を変更してや。

### 5. Composer パッケージのインストール

ローカルに PHP 8.3 以上が入ってれば:

```bash
composer install
```

入ってない場合は Docker 経由でいくで:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 6. Sail でコンテナ起動

```bash
./vendor/bin/sail up -d
```

> 💡 初回はイメージビルドで3〜5分かかるで。あんみつでも食って待っとき🍨。

### 7. アプリケーションキーの生成 & DB初期化

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
```

### 8. npm パッケージのインストール

```bash
./vendor/bin/sail npm install
```

### 9. Vite 開発サーバの起動（別ターミナル）

```bash
./vendor/bin/sail npm run dev
```

### 10. ブラウザでアクセス

`.env` の `APP_PORT` で指定したポートにアクセス（デフォルトは `http://localhost:8081`）。

買い物リストの画面が表示されたら成功や 🎉 さすガネーシャ…じゃなくて、さすが、お前や！

---

ほな、さっそく `task-1`(docs/task-1.md参照) から始めるで。お前の成長、ワシ楽しみにしとるからな。

お供えのあんみつは随時受付中や🍨。困ったら持ってきや、優しく教えたるで。
はい、Oh, My God!! ←親友の釈迦と決めポーズや。


## 付録
### 🛠️ よく使うコマンド

| やりたいこと | コマンド |
|---|---|
| コンテナ起動 | `./vendor/bin/sail up -d` |
| コンテナ停止 | `./vendor/bin/sail down` |
| artisanコマンド | `./vendor/bin/sail artisan ...` |
| npm コマンド | `./vendor/bin/sail npm ...` |
| DBリセット&シード | `./vendor/bin/sail artisan migrate:fresh --seed` |
| ログ確認 | `./vendor/bin/sail logs -f` |

> 💡 `alias sail='./vendor/bin/sail'` を `.zshrc` に追加すると楽やで。
> ワシの教え子の坂本龍馬くんも「世の中もっと便利にせなアカン」言うてたな。それと同じや。タイピングの手間を削るのは正義や。

---

### ❓ トラブルシューティング

詰まったらここを見て。よくある罠を集めたで。

### ポートが既に使われている

別の Docker プロジェクトが 80 / 3306 / 5173 を使ってる場合、`.env` で以下を変更や:

```env
APP_PORT=8081           # ホスト側のLaravelポート
VITE_PORT=5174          # ホスト側のViteポート
FORWARD_DB_PORT=33306   # ホスト側のMySQLポート
```

変更後は `./vendor/bin/sail down && ./vendor/bin/sail up -d` で再起動してや。

> 💡 ポート衝突は「同じ道路を別の車が使ってる」状態や。別の道（ポート番号）に逃すだけ。慌てんでええで。

#### `php artisan` がローカルで動かない

このプロジェクトは PHP 8.3+ を要求するんや。ローカルの PHP が古い場合は **Sail 経由** で artisan を実行してや:

```bash
./vendor/bin/sail artisan migrate    # ✅ コンテナ内のPHPで実行される
php artisan migrate                   # ❌ ローカルPHPが古いとエラー
```

#### MySQL が立ち上がる前にマイグレーションを実行してエラー

```bash
sleep 10 && ./vendor/bin/sail artisan migrate:fresh --seed
```

> 💡 これな、MySQL の起動を待ってあげへんと「まだ寝てるのに叩き起こすな！」って怒られる状態や。`sleep 10` で 10 秒待ってから声かけるだけや。

---

### 📦 技術スタック

| 分類 | 技術 |
|---|---|
| バックエンド | Laravel 12.x |
| フロントエンド | Vue 3 + Vue Router |
| スタイリング | Tailwind CSS v4 |
| データベース | MySQL 8.4 |
| 開発環境 | Docker (Laravel Sail) |
| ビルドツール | Vite 8 |

---

### 📖 関連教材

- `docs/task-1.md` 〜 `task-5.md` 各タスクのガイド
- TypeScript入門ガイド for Vue 3ユーザー（社内資料）
- フロントエンドエラーハンドリング完全攻略（社内資料）
- OpenAPI で型を自動生成してフロントエンドで安全に使う方法（社内資料）

---

