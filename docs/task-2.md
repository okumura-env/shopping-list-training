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

## 📝 実装の手順

> ⚠️ このセクションは今後追加されます。
> 学習する内容のあらすじ:
>
> - Laravel の Resource クラスを `Spectator`/`Scribe`/`L5-Swagger` 等で OpenAPI 仕様（YAML/JSON）に書き出す
> - `openapi-typescript` 等で OpenAPI → TypeScript 型を生成
> - `resources/js/types/item.ts`（手書き）を**削除**して、自動生成された型に置き換える
> - `npm run generate:types` 等のコマンドでパイプラインを叩けるようにする
> - CI または pre-commit でズレを検出できるようにする

---

## ✅ 完了基準

- [ ] OpenAPI 仕様ファイルがバックエンドから生成できる
- [ ] OpenAPI → TypeScript 型の自動生成コマンドが動く
- [ ] `resources/js/types/item.ts`（手書き）が削除され、自動生成型に置き換わっている
- [ ] `./vendor/bin/sail npx vue-tsc --noEmit` がエラーなく通る
- [ ] ブラウザで一覧/詳細/追加/削除が今まで通り動く

---

## 💡 完了したら

```bash
git add .
git commit -m "task-2: OpenAPI 型自動生成パイプラインを導入"
git push origin okumura/task-2   # ← 自分の作業ブランチ名
```

GitHub で **親リポジトリ（upstream）の `task-3` に向けて** Pull Request を作成してください。

- **base**: `okumura-env/shopping-list-training` の `task-3`
- **compare（head）**: `<あなたのfork>/shopping-list-training` の `okumura/task-2`

⚠️ **PR はマージしないでください**。`task-3` ブランチは次の受講生のスタート地点として綺麗に保つためです。

次のタスクへ進むには `docs/task-3.md` を読んでください（冒頭にスタート手順があります）。
