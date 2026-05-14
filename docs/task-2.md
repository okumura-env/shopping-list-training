# タスク2: OpenAPI 型自動生成パイプラインの構築

## 🎯 このタスクのゴール

タスク1で**手書きで書いた `interface Item`** を捨てて、Laravel の Resource から **OpenAPI 仕様を生成 → そこから TypeScript の型を自動生成** するパイプラインに置き換えます。

> 💡 タスク1の最後で見た「手書きの型は嘘をつく」問題（interface が実際のレスポンスと食い違っても気づけない）を、**バックエンドを唯一の真実とする型の自動同期**で解決するのが狙いです。

---

## 🌿 まず作業ブランチを切る

実装を始める前に、**upstream（親リポジトリ）から最新の `task-2` を取得**してから、自分の作業ブランチを切ります。ブランチ名は **`<あなたの名前>/task-2`** の形式にしてください（例: `okumura/task-2`）。

```bash
git fetch upstream
git checkout task-2
git pull upstream task-2           # 最新のスタート地点を取り込む
git checkout -b okumura/task-2     # ← 自分の名前に置き換えてください
```

> 💡 `task-2` ブランチは **タスク1を完了した状態（TypeScript化済み）** がスタート地点です。

---

## ✏️ 本題: 型を自動生成する

このタスクで使うライブラリ:

- **`dedoc/scramble`**（バックエンド）— Laravel のコードから **OpenAPI 仕様（JSON）を自動生成**。PHP の型ヒントから推論するのでアノテーションを書く必要がない
- **`openapi-typescript`**（フロントエンド）— OpenAPI 仕様 → TypeScript の型定義を生成

### Step 1: TypeScript を 5.x に揃える（前提）

`openapi-typescript` の v7 は **TypeScript 5.x が peer dependency** になっています。
タスク1で何も指定せずに `npm install -D typescript` した場合、最新の TS 6 が入っているはずです。これを 5.x に揃えます。

```bash
./vendor/bin/sail npm install -D typescript@^5
```

> 💡 ライブラリ同士のバージョン整合性は実務でしばしば出る課題です。「最新を入れたら依存先がついてこない」みたいなことが起きます。`peerDependencies` を読む習慣をつけましょう。

### Step 2: Scramble をインストール

Laravel のコードから OpenAPI 仕様（JSON）を自動生成してくれるライブラリ。

```bash
./vendor/bin/sail composer require dedoc/scramble
```

インストール後、`http://localhost:8081/docs/api.json` で OpenAPI 仕様の JSON が取得できるようになります。

### Step 3: routes/web.php を修正

Scramble が出す `/docs/api.json` を openapi-typescript が読みに行けるようにするため、`routes/web.php` の catch-all 正規表現を `^(?!api).*$` → `^(?!api|docs).*$` に変更する。

```php
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api|docs).*$');
```

### Step 4: OpenAPI 仕様の中身を見る

Scramble が `/docs/api.json` に出す OpenAPI 3.1.0 形式の JSON を覗いてみましょう:

```bash
curl -s http://localhost:8081/docs/api.json | python3 -m json.tool | head -80
```

出力されるトップレベルの構造はこんな感じ:

| キー | 意味 |
|---|---|
| `openapi` | OpenAPI 規格のバージョン宣言（`"3.1.0"`） |
| `info` | API のメタ情報（タイトル、バージョン） |
| `servers` | API のベース URL（例: `http://localhost:8081/api`） |
| `paths` | **各 URL に対する操作**。例えば `/items` の `get`（一覧取得） `post`（作成）、`/items/{item}` の `get` `put` `delete` |
| `components.schemas` | **共通の型定義**（`Item` などはここに置かれる） |

例えば `paths./items` の `get` を見ると、`responses.200.content.application/json.schema` に `"$ref": "#/components/schemas/Item"` と書かれています。これは「**この API は `Item` 型を返す**」という意味で、Item の具体的なプロパティは `components.schemas.Item` の方に書かれている、という参照関係になっています。

#### Item スキーマの中身

`components.schemas.Item` のところに Item の型情報があります:

```json
"Item": {
  "type": "object",
  "properties": {
    "id":         { "type": "integer" },
    "name":       { "type": "string" },
    "quantity":   { "type": "integer" },
    "memo":       { "type": ["string", "null"] },
    "purchased":  { "type": "boolean" },
    "created_at": { "type": ["string", "null"], "format": "date-time" },
    "updated_at": { "type": ["string", "null"], "format": "date-time" }
  }
}
```

> 💡 タスク1で**手書き**した `interface Item` と比べてみてください。
> - `created_at: string | null` ← Scramble は DB の nullable まで拾ってくれている（手書きでは `string` でズレていた）
> - これが「**バックエンドの真実をそのまま伝える**」ということです。

### Step 5: openapi-typescript を導入

```bash
./vendor/bin/sail npm install -D openapi-typescript
```

### Step 6: 型生成スクリプトを `package.json` に追加

`scripts` セクションに `generate:types` コマンドを追加:

```json
"scripts": {
  "build": "vite build",
  "dev": "vite",
  "generate:types": "openapi-typescript http://laravel.test/docs/api.json -o resources/js/types/api.d.ts"
}
```

> 💡 ホスト名が `laravel.test` なのは、Sail コンテナ内から Laravel コンテナを参照するため。`localhost` はコンテナ内では「自分自身（npm が動いているコンテナ）」を指すので使えません。

### Step 7: 型を生成

```bash
./vendor/bin/sail npm run generate:types
```

`resources/js/types/api.d.ts` が生成されます。これが OpenAPI から自動生成された **TypeScript の真実** です。中を覗いてみると、`components.schemas.Item` などの型が並んでいます。

### Step 8: 手書き interface Item を捨てる

`resources/js/types/item.ts` を以下のように書き換えます:

```ts
import type { components } from './api'

export type Item = components['schemas']['Item']
```

これだけです。**手書きの interface はすべて消えました。**

- `Item` という型は、生成された `components['schemas']['Item']` の単なる別名
- バックエンドが変わったら `npm run generate:types` を再実行 → `Item` も自動で更新
- 各 `.vue` / `.ts` ファイルは `import type { Item } from '../types/item'` のままで動く（公開 API は変わらない）

### Step 9: 型チェックを通す

```bash
./vendor/bin/sail npx vue-tsc --noEmit
```

エラーが出なければ置き換えは成功です。ブラウザで一覧/詳細/追加/削除が今まで通り動くことも確認してください。

---

## 🔥 もう一度: 自動同期を体験

タスク1の冒頭でやった**バックエンド変更**を、今度はもう一度やってみます。今は何が違うでしょうか？

### 実験: `name` を `item_name` に変えてみる

`app/Http/Controllers/Api/ItemController.php` の `index()` を以下に書き換え:

```php
public function index()
{
    return Item::orderBy('id', 'desc')->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'item_name' => $item->name,   // ← name を item_name に変えた！
            'quantity' => $item->quantity,
        ];
    });
}
```

ブラウザでリロード…**画面はやっぱり静かに壊れます**（商品名が消える）。タスク1と同じです。

#### 型を再生成してみる

```bash
./vendor/bin/sail npm run generate:types
```

そして型チェック:

```bash
./vendor/bin/sail npx vue-tsc --noEmit
```

**エラーが出るはずです** ✨

なぜなら、`ItemController::index()` のレスポンスシェイプが変わったので、OpenAPI に書かれた `Item` 型から `name` が消えて `item_name` に置き換わったから。
`ItemListView.vue` で `item.name` を参照しているコードが、もう存在しないプロパティを使っていることになります。

> 💀 **これが自動生成の威力**。
> タスク1の手書き時代は、バックエンドを変えてもフロントエンドは静かに壊れていました。
> 今は、バックエンドを変えた瞬間に「フロントエンドの何が壊れるか」が型エラーとして全部見えます。

### 元に戻す

実験が終わったら、`ItemController::index()` を元のコード（`return Item::orderBy('id', 'desc')->get();`）に戻して、`npm run generate:types` を再実行してください。`vue-tsc --noEmit` がエラーなく通る状態にしておきます。

---

## ✅ 完了基準

- [ ] `dedoc/scramble` がインストール済み
- [ ] `routes/web.php` の catch-all が `docs` も除外している
- [ ] `/docs/api` で Swagger UI が表示できる
- [ ] `package.json` に `generate:types` スクリプトがある
- [ ] `npm run generate:types` で `resources/js/types/api.d.ts` が生成される
- [ ] `resources/js/types/item.ts` から手書きの interface が消え、生成型を re-export している
- [ ] `./vendor/bin/sail npx vue-tsc --noEmit` がエラーなく通る
- [ ] ブラウザで一覧/詳細/追加/削除が今まで通り動く

---

## 💡 完了したら

```bash
git add .
git commit -m "task-2: OpenAPI 型自動生成パイプラインを導入"
git push origin okumura/task-2   # ← 自分の作業ブランチ名
```

GitHub で **親リポジトリ（upstream）の `task-2` に向けて** Pull Request を作成してください。

- **base**: `okumura-env/shopping-list-training` の `task-2`
- **compare（head）**: `<あなたのfork>/shopping-list-training` の `okumura/task-2`

⚠️ **PR はマージしないでください**。`task-2` ブランチは次の受講生のスタート地点として綺麗に保つためです。

次のタスクへ進むには `docs/task-3.md` を読んでください（冒頭にスタート手順があります）。
