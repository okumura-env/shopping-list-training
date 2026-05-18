# タスク1: TypeScript化（手書きで型を当てる）

## 🎯 このタスクのゴール

おはようさん、ワシや、ガネーシャや。🎵ガネ・ガネ・ガネーシャモーニング🎵を歌いながらトイレから出てきたとこや。

さて今日はな、お前にやってもらうのは Vue 3 + JS で書かれた買い物リストアプリを **TypeScript化** することやで。

ただしな、いきなり完璧な仕組みを使うんやのうて、まずは型を **手書きで** 当てる。「えー、手書きとかダルい」って顔してるな？分かるで、ワシも分かる。でもな、便利さだけ味わったら **ホンマのありがたみは絶対に分からん** のや。

ワシの教え子のエジソンくんもな、電球を発明するまで 1000 回失敗したんやで。「光らへん…なんで光らへんねん…」言うてぶつぶつ言いながら 1000 回や。でもその 1000 回があったから、光った瞬間「ウオオオ」っちゅう感動があったわけや。

お前も今は手書きで苦しんで、次のタスクで自動生成のありがたみに泣くんや。さぁ、いくで！

---

## 🌿 まず作業ブランチを切る

何ごとも下準備が大事や。料理する前にまな板を綺麗にするやろ？それと同じことや。

実装を始める前に、**upstream（親リポジトリ）から最新の `task-1` を取得** してから、自分の作業ブランチを切るんやで。ブランチ名は **`<お前の名前>/task-1`** の形式や（例: `okumura/task-1`）。

```bash
git fetch upstream
git checkout task-1
git pull upstream task-1           # 最新のスタート地点を取り込む
git checkout -b okumura/task-1     # ← 自分の名前に置き換えるんやで
```

> 💡 各タスクの開始時に upstream から最新を取り込む癖、これな、つけといた方がええで。
> ワシの教え子の織田信長くんも「いやいやワシは天下取るだけで精一杯やし」言うて、最新情勢を見ずに本能寺に泊まったら、どうなったか覚えてるやろ？まあ、ちゃんと最新を取り込んだ方が安全っちゅうことや。

### なぜ `task-1` で直接作業しないのか

- `task-1` ブランチは **スタート地点** として残しとくんや。やり直したくなったら `git checkout task-1` で戻れるからな。
- PR を出すとき「`okumura/task-1` → `task-1`」と from/to が一目で区別できるからや。
- `task-1` ブランチに更新があったときに `git pull upstream task-1` で更新を取り込めるよう、自分のコミットで汚さんようにする意図もあるんや。😎

---

## 👀 まずは現状を確認

ブラウザで `http://localhost:8081`（`.env` の `APP_PORT` に合わせてな）を開いてみい。買い物リストが表示されてるはずや。

これがな、お前のスタート地点や。ここを TypeScript の世界に引きずり込んでいくんやで。

実装ファイル:
- `resources/js/views/ItemListView.vue` — 一覧画面（JS、型なし）
- `resources/js/views/ItemDetailView.vue` — 詳細画面（JS、型なし）
- `resources/js/router/index.js` — Vue Router 設定
- `resources/js/api/client.js` — axios インスタンス
- `resources/js/api/items.js` — API関数 (listItems / getItem / createItem / deleteItem)
- `app/Http/Controllers/Api/ItemController.php` — バックエンドCRUD

ぜーんぶ拡張子が `.js` か `.php` やろ？「型」っちゅう言葉が一切出てこんのが今のお前の世界や。ふふん、これからこれを変えていくで。

---

## 🔥 ウォーミングアップ: 型なしの痛みを体験

実装に入る前にな、ちょっと「型なし JS の気持ち悪さ」を体験させたるわ。

これが地味に大事なんやで。ワシの教え子のソクラテスくんが「無知の知」言うてたやろ？「自分が何を知らんかを知ること」が学びの第一歩や。今のお前は「型がないこと」が何を意味するかまだ分かってへん。だからまずそれを体感してもらう。

### 手順

1. `app/Models/Item.php` を開いて、`$hidden` プロパティを **1行追加** してみい:

   ```php
   class Item extends Model
   {
       use HasFactory;

       protected $fillable = ['name', 'quantity', 'memo', 'purchased'];

       protected $hidden = ['name'];   // ← この行を新規追加！

       protected $casts = [
           'quantity' => 'integer',
           'purchased' => 'boolean',
       ];
   }
   ```

   > 💡 `$hidden` っちゅうのは Laravel の標準機能で、「**Model を JSON に変換するときに隠したいフィールド**」を指定する場所や。実務では `password` とか `api_token` みたいな秘匿情報を API レスポンスから除外するために使う。
   >
   > ところで実務では、こんな場面でも **フィールドが API レスポンスから消える** ことがあるんやで:
   > - migration で `dropColumn('name')` してカラム自体を削除した（DB から無くなる）
   > - API Resource クラスの `toArray()` から `'name' => ...` の行を消した（出力対象から外す）
   > - API Resource クラスで `'item_name' => $this->name` に rename した（旧名 `name` が消える）
   >
   > フロントエンドから見ると **どれも同じ症状** や：「昨日まで API JSON に `name` があったのに、今日見たら無い」。
   > 今回はその症状を `$hidden = ['name']` の1行でサクッと再現しとるんや。商品の `name` を本当に hide するのは実務的にあり得んけど、上記みたいなシナリオの **代理実験** や思てくれ。

2. ブラウザをリロードして画面を確認や。

### 何が起こったか

- エラーは出ない
- でも商品名がどこにも表示されない
- `{{ item.name }}` を読んでたテンプレートは、`item.name` が `undefined` になっただけ

**これが JS の世界やで。** バックエンドの API レスポンスが変わったことに **実行するまで気づかへん**。

> 💀 これはな、PDF「OpenAPI で型を自動生成して...」で語られてる "手書きの型は嘘をつく" の **前段階** や。
> "そもそも型がないから何も検出できん" っちゅう、もっと深い闇の状態やで。怖いやろ？

### 元に戻す

`app/Models/Item.php` から `protected $hidden = ['name'];` の1行を削除してや。それだけ。

「あれ、戻すんかい」って思ったやろ？そうや、今のは予告編や。本編はこれからやで。

---

## ✏️ 本題: TypeScript化する

ここから本気出すで。お前の JS 世界に TypeScript の鎧を着せていく作業や。

### Step 1: 必要なパッケージを入れる

```bash
./vendor/bin/sail npm install -D typescript vue-tsc @types/node
```

それぞれ何のパッケージかワシが教えたろ。

- **`typescript`**: 本体。これがないと何も始まらん。
- **`vue-tsc`**: Vue ファイル（.vue）の中の TS をチェックしてくれるやつ。普通の `tsc` だと `.vue` は見えへんからな。
- **`@types/node`**: Node.js の API（`process` とか）の型定義。これがないと「process って何やねん」言うてエディタが赤波線出すで。

ルートディレクトリに `tsconfig.json` を作成や:

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

これはな、TypeScript への「お前はこういうルールで仕事してや」っちゅう **指示書** みたいなもんや。特に `"strict": true` がポイントで、これがあると TS が「妥協なし」モードに入るんやで。

* 赤波線が出るかもしれんが、ts ファイルがまだないからや。Step 2 で解消するで。

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

> ⚠️ 初心者あるある：jsをtsにリネームしただけで満足する人おるけど、今みたいに`vite.config.js` と `app.blade.php` の指定も変えなアカンで。ここ忘れると「あれ、画面真っ白やん」ってなって泣くからな。

### Step 3: 以下の3ファイルに `lang="ts"` を追加

- `resources/js/App.vue`
- `resources/js/views/ItemListView.vue`
- `resources/js/views/ItemDetailView.vue`

```vue
<script setup lang="ts">
// ... 既存のコード
</script>
```

`lang="ts"` を付けることで、Vue ファイルの中身も TypeScript として扱われるようになるんや。「ここから TS の世界やで」っちゅう宣言みたいなもんや。

*この時点で `ItemListView.vue` の `async function removeItem(item){` の `item` などに赤波線が出るで（型推論できない引数があるためや）。

ここで「うわ、赤線出た！壊れた！」って焦るやろ？焦るな。TS が「お前、引数の型書いてへんで」って親切に教えてくれてるだけや。次のステップで型をちゃんと付けたら消えるからな。

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

ここ、めっちゃ大事や。`interface` っちゅうのはな、「Item ちゅうもんはこういうフィールド持っとるで！」っちゅう **約束** や。

ワシの教え子のピタゴラスくんも「数の世界には決まりがある」言うてたけど、それと同じで、TS の世界では **「型」が決まり** やねん。

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

`ref<Item[]>([])` で「これは Item の配列やで」、`removeItem(item: Item)` で「引数の `item` は Item 型やで」と TS に教えてるんや。

*ここで `removeItem` 等の赤波線が解消されるはずや。「あ、消えた！」っちゅう快感、これがプログラマーの密かな喜びやで。


`resources/js/views/ItemDetailView.vue`:

```ts
import type { Item } from '../types/item'

const item = ref<Item | null>(null)
```

> 💡 ここでは初期値が `null`（API 取得前は何もない）なので、型を `Item | null` にしてるんやで。
> その結果、`item.value` の型は **`Item | null`** になるから、TypeScript は **`null` の可能性を考慮した書き方を要求** してくる。

そのため `remove()` 関数で `item.value.name` のように直接アクセスすると「`item.value` が `null` の可能性があるで」っちゅうエラー（赤波線）が出るんや。先頭で **null チェック** を入れて早期 return しよか:

```ts
async function remove() {
  if (!item.value) return                                          // ← この行を追加するんやで
  if (!confirm(`「${item.value.name}」を削除しますか？`)) return
  await deleteItem(item.value.id)
  router.push('/')
}
```

> 💀 これは TypeScript の **strict null checks** が効いてる状態や。「null かも？」を見逃さんことで、ロード前にボタン連打したときの画面クラッシュみたいなバグを防いでくれる。
> ワシの教え子のナポレオンちゃんも「備えあれば憂いなし」言うてたけど、null チェックはまさにそれや。

### Step 6: API関数にも型を付ける

`resources/js/api/items.ts`:
* すでに `createItem(data)` の `data` や `deleteItem(id)` の `id` に赤波線が出てるはずや。

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

* ここまで書いたら、API関数の引数の赤波線も解消されるで。

#### ⚠️ 代わりに ItemDetailView.vue で新しい赤波線が出るはず

`getItem(id: number)` と型を付けた瞬間、**呼び出し側** で型エラーが出るんや。

> 💀 これも TypeScript が「型が合うてへんで」って教えてくれた例や。
> JS の時は文字列の `"3"` をそのまま URL に埋め込んでも動いてたから、バグに気づかんかった。けど `getItem` の引数を `number` と宣言したことで、**呼び出し側の不整合まで芋づる式に見えるようになった** わけや。
> ワシの教え子のレオナルド・ダ・ヴィンチくんが「全てはつながっとる」言うてたけど、まさにそれや。

```resources/js/views/ItemDetailView.vue
async function loadItem() {
  const response = await getItem(route.params.id)  // ← ここに赤波線
  item.value = response.data
}
```

エラーの中身はこんな感じや:

> Argument of type `'string | string[]'` is not assignable to parameter of type `'number'`.

理由はな、`route.params.id` の型が **`string | string[]`** やからや。URL のパラメータは仕様上「常に文字列」やから、Vue Router はそう型付けしてるんや（`/items/3` の `3` も TS から見れば文字列の `"3"` や）。

`Number()` で変換しよか:

```resources/js/views/ItemDetailView.vue
async function loadItem() {
  const response = await getItem(Number(route.params.id))   // ← Number() で囲むんやで
  item.value = response.data
}
```

### Step 7: 型チェックを通す

```bash
./vendor/bin/sail npx vue-tsc --noEmit
```

エラーが出んくなったら OK や。出る場合は型注釈を直していくんやで。

> 💡 `--noEmit` っちゅうのは「型チェックだけして、ファイルは生成すんなや」っちゅう意味や。Vite が変換は別でやってくれるから、TS は型チェック専門でええわけや。

---

## 💡 TypeScriptは何を見ているか

TS化お疲れさん！`vue-tsc --noEmit` がエラーなしで通ったやろ！

「これでミスする心配はなくなった！よかった〜！あんみつ食お！」

...って思うやろ？早とちりやで🤔

一度立ち止まって考えてみい↓↓↓↓↓

> TypeScript は、**何**をチェックしてるんでしょうか？

答えはな、**お前が書いた `interface Item` の内容** や。(resources/js/types/item.ts)

```
[実際のバックエンドが返すデータ]      [interface Item]      [.vue / .ts のコード]
        ❓ ←─── ここは見てない ───→     ✅ ←─── ここはチェック ───→ ✅
```

- TS は「interface 通りにコードが書けてるか」をチェックしてる
- TS は「interface がバックエンドと合うてるか」は **チェックしてへん**
- なんでかって？`interface` は **お前が手で書いた仮説** やから、TS はそれを真実として信じるしかないんや

つまり、**interface が嘘をついてた場合、TS は "嘘の真実" をチェックしてるだけ** になる。

これは結構ヤバいやろ？次の節で実際に確かめてみよか。

---

## 🔥 もう一度: 手書きの限界を体験

「TS は interface しか見てへん」っちゅうことを、2つの実験で確認するで。

### 実験1: TSが嘘を見抜けないことを確認する

#### 手順

1. **ウォーミングアップと同じ変更** や。`app/Models/Item.php` に `$hidden = ['name']` の1行を追加:

   ```php
   class Item extends Model
   {
       use HasFactory;

       protected $fillable = ['name', 'quantity', 'memo', 'purchased'];

       protected $hidden = ['name'];   // ← ウォーミングアップと同じ1行を追加

       protected $casts = [
           'quantity' => 'integer',
           'purchased' => 'boolean',
       ];
   }
   ```

2. **`interface Item`(resources/js/types/item.ts) は何もいじらん**（`name: string` のまま）

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

- `interface` は「`name` というフィールドがあるで」と言ってる → コードも `item.name` を読んでる → **TS 的には全部合格**
- でも実際のバックエンドは `$hidden` で `name` を返さなくなった → 実行時に `item.name` は `undefined`
- **TS は嘘を見抜けへん**。interface を信じてチェックするからや

ふふん、騙されたやろ？型ついててもバグるんやで。これが「手書きの限界」の第一歩や。

---

### 実験2: 直そうとすると手間が見える

今度は interface を実際のバックエンドに合わせて修正してみよか。`name` フィールドがバックエンドから返って来なくなったんやから、interface からも消すで:

#### 手順

1. `resources/js/types/item.ts` を編集して `name: string` の行を削除:

   ```ts
   export interface Item {
     id: number
     // name: string         ← この行を削除（バックエンドが返さなくなった）
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

今度は **たくさんエラーが出る** はずや。例えば:

```
ItemListView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemListView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemDetailView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemDetailView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemDetailView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
ItemDetailView.vue:XX:XX - error TS2339: Property 'name' does not exist on type 'Item'.
```

エディタを開いても、`ItemListView.vue` と `ItemDetailView.vue` の両方にまたがって、`item.name` を使ってる箇所が **全て赤く** なるはずや（template の `{{ item.name }}`、削除確認の `${item.name}`、見出しなど合計6箇所くらい）。

#### ここで気づくこと

- **TS は「型が変わったら、影響範囲を全部教えてくれる」** ← これが TS の強み 💪
- **複数ファイルにまたがって** 漏れなく検出される。grep で文字列検索するのとは違うで、**意味的に紐づいた箇所だけ** を正確に教えてくれるんや
- でもな、エラーを1つずつ潰すのは **地味に大変** や。これがフィールド30個 × 各20箇所だったら…？想像しただけで疲れるやろ
- そして **「interface を実際のバックエンドに合わせて手で更新する」のはお前の責任** や

---

### 結論: 何が「手書きの限界」か

| TS が守ってくれること | TS が守ってくれへんこと |
|---|---|
| ✅ interface の内容と、それを使うコードの整合性 | ❌ interface とバックエンドの整合性 |
| ✅ 型を変えたら影響範囲を教えてくれる | ❌ 「バックエンドが変わった」ことの検知 |

**型を書くことで安全性は得られる。でも「型自体を正しく保つ責任」はお前に残るんや。**

フィールドが100個あって、バックエンドが頻繁に変わるプロジェクトでは、これは現実的やない。

> 💡 これを解決するのが次のタスク（OpenAPI 型自動生成）や。バックエンドのコードから型を自動生成することで、**interface がバックエンドの真実から自動で降りてくる** ようになるんや。
> はい、Oh, My God!! 🐘🧘←親友の釈迦と決めポーズや。

---

### 元に戻す

実験が終わったら、以下を元に戻してや:

1. `app/Models/Item.php` から `protected $hidden = ['name'];` の1行を削除
2. `resources/js/types/item.ts` の `interface Item` に `name: string` を復活
3. `vue-tsc --noEmit` がエラーなく通り、ブラウザで商品名が再び表示されることを確認

---

## ✅ 完了基準

- [ ] `tsconfig.json` が存在し、`./vendor/bin/sail npx vue-tsc --noEmit` がエラーなく通る
- [ ] `app.ts` / `client.ts` / `items.ts` / `router/index.ts` にリネーム済み
- [ ] `.vue` ファイルすべてに `lang="ts"` が付いている
- [ ] `interface Item` を `resources/js/types/item.ts` に定義した
- [ ] `ref<Item[]>([])` のように型を使っている
- [ ] API関数 (`listItems` 等) の返り値型が `Item[]` または `Item` になっている
- [ ] ブラウザで一覧/追加/削除が今まで通り動く

全部チェックついたか？さすガネーシャや！…と言いたいとこやけど、これは全部お前の手柄やで。よう頑張った。

---

## 💡 完了したら

```bash
git add .
git commit -m "task-1: Vue を TypeScript 化"
git push origin okumura/task-1   # ← "okumura"の部分は自分の名前に変えるんやで
```

GitHub で **親リポジトリ（upstream）の `task-1` に向けて** Pull Request を作成してや。
> 別リポジトリにPull Requestすることに違和感あるかもしれへんけど
> 親リポジトリ(upstream)にはPull Requestできるようになってるんやで！🐘🍨

- **base**: `okumura-env/shopping-list-training` の `task-1`
- **compare（head）**: `<あなたのfork>/shopping-list-training` の `<名前>/task-1`


⚠️ **Pull Request はマージしないでな**。`task-1` ブランチは次の受講生のスタート地点として綺麗に保つためや。

次のタスクへ進むには `docs/task-2.md` を読んでや（冒頭にスタート手順があるで）。

タスク2ではな、この章で書いた手書きの `interface Item` を **捨てて**、Laravel の Resource から **自動生成された型** に置き換えるで。
「えー、せっかく書いたのに捨てんの？」って思うやろ？それがな、「手書きを捨てる」ありがたみの体験なんや。

ワシのお供えに、あんみつ持ってきてくれたら、もうちょっと優しく教えたるで🍨。
