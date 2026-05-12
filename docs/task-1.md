# タスク1: TypeScript化（手書きで型を当てる）

## 🎯 このタスクのゴール

Vue 3 + JS で書かれた買い物リストアプリを **TypeScript化**します。型を**手書きで**当てることで、TS の便利さと、手書きの限界の両方を体験します。

---

## 👀 まずは現状を確認

ブラウザで `http://localhost:8081`（`.env`の`APP_PORT`に合わせて）を開いてください。買い物リストが表示されているはずです。

実装ファイル:
- `resources/js/views/ItemListView.vue` — 画面コンポーネント（JS、型なし）
- `resources/js/api/client.js` — axios インスタンス
- `resources/js/api/items.js` — API関数 (listItems / createItem / deleteItem)
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

`tsconfig.json` を作成:

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

### Step 3: `.vue` ファイルに `lang="ts"` を追加

```vue
<script setup lang="ts">
// ... 既存のコード
</script>
```

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

### Step 5: `ItemListView.vue` で型を使う

```ts
import type { Item } from '../types/item'

const items = ref<Item[]>([])
const newName = ref<string>('')
const newQuantity = ref<number>(1)

async function removeItem(id: number) {
  // ...
}
```

### Step 6: API関数にも型を付ける

`resources/js/api/items.ts`:

```ts
import type { Item } from '../types/item'
import apiClient from './client'

export function listItems() {
  return apiClient.get<Item[]>('/items')
}

export function createItem(data: { name: string; quantity: number }) {
  return apiClient.post<Item>('/items', data)
}

export function deleteItem(id: number) {
  return apiClient.delete(`/items/${id}`)
}
```

### Step 7: 型チェックを通す

```bash
./vendor/bin/sail npx vue-tsc --noEmit
```

エラーが出なくなったらOK。出る場合は型注釈を直していきます。

---

## 🔥 もう一度: 手書きの限界を体験

TS化が終わったら、もう一度バックエンドを壊してみましょう。

### 手順

1. `ItemController.php` の `index()` で、また `name` を `item_name` に変える

2. もう一度ブラウザでアクセス

### 今度は何が起こるか

- ブラウザの画面は **やっぱり何も表示されない**（実行時のデータが間違っているから）
- ただし、もしあなたが `interface Item` の `name: string` を **手で `item_name: string` に書き換えていれば**、エディタの補完で正しいフィールド名を打ち間違えなくなります

### ここがミソ

- 型は確かに**書き間違い**を防いでくれる
- しかし、**バックエンドが変わったときに、あなた自身が `interface` を手で直す必要がある**
- フィールドが100個あったら？ 毎回手で同期？

> 💀 これが「手書きの型は嘘をつく」状態です。型は守ってくれるけど、**型を書く責任**があなたに残っている。

**この問題を解決するのが次のタスク（OpenAPI型自動生成）です。**

### 元に戻す

`ItemController.php` と、もし変更したなら `interface Item` も元に戻してください。

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
