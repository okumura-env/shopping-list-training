# タスク1: TypeScript化（手書きで型を当てる）

## 🎯 このタスクのゴール

Vue 3 + JS で書かれた買い物リストアプリを **TypeScript化**します。型を**手書きで**当てることで、TS の便利さと、手書きの限界の両方を体験します。

---

## 👀 まずは現状を確認

ブラウザで `http://localhost:8081`（`.env`の`APP_PORT`に合わせて）を開いてください。買い物リストが表示されているはずです。

実装ファイル:
- `resources/js/views/ItemListView.vue` — 一覧画面（JS、型なし）
- `resources/js/views/ItemDetailView.vue` — 詳細画面（JS、型なし）
- `resources/js/router/index.js` — Vue Router 設定
- `resources/js/api/client.js` — axios インスタンス
- `resources/js/api/items.js` — API関数 (listItems / getItem / createItem / deleteItem)
- `app/Http/Controllers/Api/ItemController.php` — バックエンドCRUD

---

## 🔥 ウォーミングアップ: 型なしの痛みを体験

実装に入る前に、JS の世界の「気持ち悪さ」を体験してもらいます。

### 手順

1. `app/Http/Controllers/Api/ItemController.php` を開き、`index()` メソッドを以下のように改変してください:

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

2. ブラウザをリロードして画面を確認してください。

### 何が起こったか

- エラーは出ない
- でも商品名がどこにも表示されない
- `{{ item.name }}` を読んでいたテンプレートは、`item.name` が `undefined` になっただけ

**これが JS の世界です。** バックエンドとフロントエンドの型の食い違いに**実行するまで気づけない**。

> 💀 これは PDF「OpenAPI で型を自動生成して...」で語られている "手書きの型は嘘をつく" の前段階、"そもそも型がないから何も検出できない" 状態です。

### 元に戻す

`ItemController.php` を元のコード（`return Item::orderBy('id', 'desc')->get();`）に戻してください。

---

## ✏️ 本題: TypeScript化する

### Step 1: 必要なパッケージを入れる

```bash
./vendor/bin/sail npm install -D typescript vue-tsc @types/node
```

ルートディレクトリに`tsconfig.json` を作成:

```json
{
  "compilerOptions": {
    "target": "ESNext",
    "module": "ESNext",
    "moduleResolution": "Bundler",
    "strict": true,
    "jsx": "preserve",
    "esModuleInterop": true,
    "allowSyntheticDefaultImports": true,
    "skipLibCheck": true,
    "isolatedModules": true,
    "resolveJsonModule": true,
    "types": ["vite/client"]
  },
  "include": ["resources/js/**/*.ts", "resources/js/**/*.vue"]
}
```
* 赤波線が出る可能性があるがtsファイルがないから。Step2で解消

### Step 2: `.js` ファイルを `.ts` にリネーム

```bash
mv resources/js/app.js resources/js/app.ts
mv resources/js/router/index.js resources/js/router/index.ts
mv resources/js/api/client.js resources/js/api/client.ts
mv resources/js/api/items.js resources/js/api/items.ts
```

`vite.config.js` の入力ファイルを `app.ts` に変更:

```js
input: ['resources/css/app.css', 'resources/js/app.ts'],
```

`resources/views/app.blade.php` の `@vite` ディレクティブも修正:

```blade
@vite(['resources/css/app.css', 'resources/js/app.ts'])
```

### Step 3: 以下の3ファイルに `lang="ts"` を追加

- `resources/js/App.vue`
- `resources/js/views/ItemListView.vue`
- `resources/js/views/ItemDetailView.vue`

```vue
<script setup lang="ts">
// ... 既存のコード
</script>
```
*この時点で `ItemListView.vue` の `async function removeItem(item){` の `item` などに赤波線が出る（型推論できない引数があるため）。

### Step 4: `interface Item` を定義

新しいファイル `resources/js/types/item.ts` を作成:

```ts
export interface Item {
  id: number
  name: string
  quantity: number
  memo: string | null
  purchased: boolean
  created_at: string
  updated_at: string
}
```

### Step 5: 各 `.vue` ファイルで型を使う

`resources/js/views/ItemListView.vue`:

```ts
import type { Item } from '../types/item'

const items = ref<Item[]>([])
const newName = ref<string>('')
const newQuantity = ref<number>(1)

async function removeItem(item: Item) {
  // ...
}
```
*ここで `removeItem` 等の赤波線が解消されます。


`resources/js/views/ItemDetailView.vue`:

```ts
import type { Item } from '../types/item'

const item = ref<Item | null>(null)
```

> 💡 ここでは初期値が `null`（API 取得前は何もない）なので、型を `Item | null` にしています。
> その結果、`item.value` の型は **`Item | null`** になるため、TypeScript は **`null` の可能性を考慮した書き方を要求**します。

そのため `remove()` 関数で `item.value.name` のように直接アクセスすると「`item.value` が `null` の可能性があるよ」というエラー（赤波線）が出ます。先頭で **null チェック** を入れて早期 return しましょう:

```ts
async function remove() {
  if (!item.value) return                                          // ← この行を追加
  if (!confirm(`「${item.value.name}」を削除しますか？`)) return
  await deleteItem(item.value.id)
  router.push('/')
}
```

> 💀 これは TypeScript の **strict null checks** が効いている状態。「null かも？」を見逃さないことで、たとえばロード前にボタンを連打したときの画面クラッシュみたいなバグを防いでくれます。

### Step 6: API関数にも型を付ける

`resources/js/api/items.ts`:
* すでに `createItem(data)` の `data` や `deleteItem(id)` の `id` に赤波線が出ているはず。

```ts
import type { Item } from '../types/item'
import apiClient from './client'

export function listItems() {
  return apiClient.get<Item[]>('/items')
}

export function getItem(id: number) {
  return apiClient.get<Item>(`/items/${id}`)
}

export function createItem(data: { name: string; quantity: number }) {
  return apiClient.post<Item>('/items', data)
}

export function deleteItem(id: number) {
  return apiClient.delete(`/items/${id}`)
}
```
* ここまで書いたら、API関数の引数の赤波線も解消されます。

### Step 7: 型チェックを通す

```bash
./vendor/bin/sail npx vue-tsc --noEmit
```

エラーが出なくなったらOK。出る場合は型注釈を直していきます。

---

## 💡 TypeScriptは何を見ているか

TS化お疲れさまでした!`vue-tsc --noEmit` がエラーなしで通りました!
「これでミスする心配はなくなった！よかった〜！」
...となるのは実はまだ早いんです🤔

一度立ち止まって考えてみましょう↓↓↓↓↓

> TypeScript は、**何**をチェックしているのでしょう？

答え: **あなたが書いた `interface Item` の内容**です。(resources/js/types/item.ts)

```
[実際のバックエンドが返すデータ]      [interface Item]      [.vue / .ts のコード]
        ❓ ←─── ここは見てない ───→     ✅ ←─── ここはチェック ───→ ✅
```

- TS は「interface通りにコードが書けているか」をチェックしている
- TS は「interface がバックエンドと合っているか」は**チェックしていない**
- なぜなら `interface` は **あなたが手で書いた仮説** であり、TS はそれを真実として信じるしかないから

つまり、**interface が嘘をついていた場合、TS は"嘘の真実"をチェックしているだけ**になります。

これが本当か、次の節で実際に確かめます。

---

## 🔥 もう一度: 手書きの限界を体験

「TS は interface しか見ていない」ことを2つの実験で確認します。

### 実験1: TSが嘘を見抜けないことを確認する

#### 手順

1. `app/Http/Controllers/Api/ItemController.php` の `index()` を改造（ウォーミングアップと同じ変更）:

   ```php
   public function index()
   {
       return Item::orderBy('id', 'desc')->get()->map(function ($item) {
           return [
               'id' => $item->id,
               'item_name' => $item->name,   // ← name を item_name に変える
               'quantity' => $item->quantity,
           ];
       });
   }
   ```

2. **`interface Item`(resources/js/types/item.ts) は何もいじらない**（`name: string` のまま）

3. ターミナルで型チェックを実行:

   ```bash
   ./vendor/bin/sail npx vue-tsc --noEmit
   ```

4. ブラウザでリロード

#### 観察ポイント

| | 状態 |
|---|---|
| `vue-tsc` の結果 | ✅ **エラーなしで通る** |
| ブラウザの画面 | ❌ 商品名が表示されない |

#### ここで気づくこと

- `interface` は「`name` というフィールドがあるよ」と言っている → コードも `item.name` を読んでいる → **TS的には全部合格**
- でも実際のバックエンドは `item_name` を返している → 実行時に `item.name` は `undefined`
- **TS は嘘を見抜けない**。interface を信じてチェックするから

---

### 実験2: 直そうとすると手間が見える

今度は interface を実際のバックエンドに合わせて修正してみましょう。

#### 手順

1. `resources/js/types/item.ts` を編集:

   ```ts
   export interface Item {
     id: number
     item_name: string   // ← name → item_name に変更
     quantity: number
     memo: string | null
     purchased: boolean
     created_at: string
     updated_at: string
   }
   ```

2. もう一度型チェック:

   ```bash
   ./vendor/bin/sail npx vue-tsc --noEmit
   ```

#### 観察ポイント

今度は**たくさんエラーが出る**はずです。例えば:

```
ItemListView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemListView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemDetailView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemDetailView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemDetailView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemDetailView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
```

エディタを開いても、`ItemListView.vue` と `ItemDetailView.vue` の両方にまたがって、`item.name` を使っている箇所が**全て赤く**なります（template の `{{ item.name }}`、削除確認の `${item.name}`、見出しなど合計6箇所くらい）。

#### ここで気づくこと

- **TS は「型が変わったら、影響範囲を全部教えてくれる」** ← これがTSの強み 💪
- **複数ファイルにまたがって**漏れなく検出される。grep で文字列検索するのとは違い、**意味的に紐づいた箇所だけ**を正確に教えてくれる
- でも、エラーを1つずつ潰すのは**地味に大変**。これがフィールド30個 × 各20箇所だったら…？
- そして**「interface を実際のバックエンドに合わせて手で更新する」のはあなたの責任**

---

### 結論: 何が「手書きの限界」か

| TS が守ってくれること | TS が守ってくれないこと |
|---|---|
| ✅ interface の内容と、それを使うコードの整合性 | ❌ interface とバックエンドの整合性 |
| ✅ 型を変えたら影響範囲を教えてくれる | ❌ 「バックエンドが変わった」ことの検知 |

**型を書くことで安全性は得られるが、「型自体を正しく保つ責任」はあなたに残る。**

フィールドが100個あって、バックエンドが頻繁に変わるプロジェクトでは、これは現実的じゃない。

> 💡 これを解決するのが次のタスク（OpenAPI型自動生成）です。バックエンドのコードから型を自動生成することで、**interface がバックエンドの真実から自動で降りてくる**ようになります。

---

### 元に戻す

実験が終わったら、以下を元に戻してください:

1. `ItemController.php` の `index()` を元に戻す（`return Item::orderBy('id', 'desc')->get();`）
2. `interface Item` の `item_name` を `name` に戻す
3. `vue-tsc --noEmit` がエラーなく通ることを確認

---

## ✅ 完了基準

- [ ] `tsconfig.json` が存在し、`./vendor/bin/sail npx vue-tsc --noEmit` がエラーなく通る
- [ ] `app.ts` / `client.ts` / `items.ts` / `router/index.ts` にリネーム済み
- [ ] `.vue` ファイルすべてに `lang="ts"` が付いている
- [ ] `interface Item` を `resources/js/types/item.ts` に定義した
- [ ] `ref<Item[]>([])` のように型を使っている
- [ ] API関数 (`listItems` 等) の返り値型が `Item[]` または `Item` になっている
- [ ] ブラウザで一覧/追加/削除が今まで通り動く

---

## 💡 完了したら

```bash
git add .
git commit -m "task-1: Vue を TypeScript 化"
git push origin work/task-1
```

GitHub で Pull Request を作成 → レビュー → マージ。

次のタスクへ:

```bash
git checkout task-2
```

タスク2では、この章で書いた手書きの `interface Item` を**捨てて**、Laravel の Resource から **自動生成された型**に置き換えます。書き直しに見えるかもしれませんが、それが「手書きを捨てる」ありがたみの体験です。
