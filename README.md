# 🛒 買い物リスト フロントエンド研修課題

Vue 3 × Laravel を使った、**TypeScript導入・OpenAPI型自動生成・エラーハンドリング**のハンズオン課題です。

## 🎯 この課題で身につくこと

1. **TypeScript の基礎** — 既存の Vue (JS) コードに型を付けていく感覚
2. **OpenAPI による型の自動生成** — バックエンドと型を常に一致させる仕組み
3. **エラーハンドリングの責務分離** — axios interceptor / composable / UIコンポーネント

---

## 📚 課題の構成

各タスクは**ブランチ**として用意されています。前のタスクの完成形が次のタスクのスタート地点になっています。

| ブランチ | 内容 |
|---|---|
| `task-1` | TypeScript化（手書きで型を当てる）👈 **ここから開始** |
| `task-2` | OpenAPI 型自動生成パイプラインの構築 |
| `task-3` | カラム追加を全レイヤーで貫通 |
| `task-4` | エラーハンドリングの実装 |
| `task-5` | バリデーション + 422表示 |
| `complete` | 全部完成形 |

各タスクの詳細は `docs/task-N.md` を参照してください。

---

## 🚀 セットアップ

### 1. リポジトリを Fork

右上の「Fork」ボタンで自分のアカウントにコピーします。

### 2. ローカルに clone

```bash
cd ~/Desktop
git clone https://github.com/<あなたのGitHubユーザー名>/shopping-list-training.git
cd shopping-list-training
```

### 3. 環境変数ファイルの準備

```bash
cp .env.example .env
```

> ⚠️ ポートが他のプロジェクトと衝突する場合は `.env` の `APP_PORT` / `VITE_PORT` / `FORWARD_DB_PORT` を変更してください。

### 4. Composer パッケージのインストール

ローカルに PHP 8.3 以上が入っていれば:

```bash
composer install
```

入っていない場合は Docker 経由:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 5. Sail でコンテナ起動

```bash
./vendor/bin/sail up -d
```

> 💡 初回はイメージビルドで3〜5分かかります。

### 6. アプリケーションキーの生成 & DB初期化

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
```

### 7. npm パッケージのインストール

```bash
./vendor/bin/sail npm install
```

### 8. Vite 開発サーバの起動（別ターミナル）

```bash
./vendor/bin/sail npm run dev
```

### 9. ブラウザでアクセス

`.env` の `APP_PORT` で指定したポートにアクセス（デフォルトは `http://localhost:8081`）。

買い物リストの画面が表示されれば成功です 🎉

---

## 🌿 課題の進め方

### 1. task-1 ブランチをチェックアウト

```bash
git checkout task-1
```

### 2. `docs/task-1.md` を読む

タスクの目的・やること・完了基準が書かれています。

### 3. 実装する

自分の作業ブランチを切って実装:

```bash
git checkout -b work/task-1
# ... コードを編集 ...
git add .
git commit -m "task-1: TypeScript化"
git push origin work/task-1
```

### 4. PR を作成

GitHub で Pull Request を作成 → レビューを受ける → マージ。

### 5. 次のタスクへ

```bash
git checkout task-2
# docs/task-2.md を読んで次へ
```

詰まったら次の `task-N+1` ブランチに前タスクの完成形があるので、そこから続行できます。

---

## 🛠️ よく使うコマンド

| やりたいこと | コマンド |
|---|---|
| コンテナ起動 | `./vendor/bin/sail up -d` |
| コンテナ停止 | `./vendor/bin/sail down` |
| artisanコマンド | `./vendor/bin/sail artisan ...` |
| npm コマンド | `./vendor/bin/sail npm ...` |
| DBリセット&シード | `./vendor/bin/sail artisan migrate:fresh --seed` |
| ログ確認 | `./vendor/bin/sail logs -f` |

> 💡 `alias sail='./vendor/bin/sail'` を `.zshrc` に追加すると楽です。

---

## ❓ トラブルシューティング

### ポートが既に使われている

別のDockerプロジェクトが80/3306/5173を使っている場合、`.env` で以下を変更:

```env
APP_PORT=8081           # ホスト側のLaravelポート
VITE_PORT=5174          # ホスト側のViteポート
FORWARD_DB_PORT=33306   # ホスト側のMySQLポート
```

変更後は `./vendor/bin/sail down && ./vendor/bin/sail up -d` で再起動。

### `php artisan` がローカルで動かない

このプロジェクトは PHP 8.3+ を要求します。ローカルのPHPが古い場合は **Sail経由**でartisanを実行してください:

```bash
./vendor/bin/sail artisan migrate    # ✅ コンテナ内のPHPで実行される
php artisan migrate                   # ❌ ローカルPHPが古いとエラー
```

### MySQL が立ち上がる前にマイグレーションを実行してエラー

```bash
sleep 10 && ./vendor/bin/sail artisan migrate:fresh --seed
```

---

## 📦 技術スタック

| 分類 | 技術 |
|---|---|
| バックエンド | Laravel 12.x |
| フロントエンド | Vue 3 + Vue Router |
| スタイリング | Tailwind CSS v4 |
| データベース | MySQL 8.4 |
| 開発環境 | Docker (Laravel Sail) |
| ビルドツール | Vite 8 |

---

## 📖 関連教材

- `docs/task-1.md` 〜 `task-5.md` 各タスクのガイド
- TypeScript入門ガイド for Vue 3ユーザー（社内資料）
- フロントエンドエラーハンドリング完全攻略（社内資料）
- OpenAPI で型を自動生成してフロントエンドで安全に使う方法（社内資料）
