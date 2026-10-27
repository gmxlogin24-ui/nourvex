#!/bin/bash
source ~/.campulsy.env
cd ~/nourvex

git checkout package-lock.json 2>/dev/null

cat > copy-sqlite.php <<'PHPEOF'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
config(['database.connections.old' => ['driver'=>'sqlite','database'=>__DIR__.'/database/old.sqlite','prefix'=>'']]);
$old = DB::connection('old');
$new = DB::connection(config('database.default'));
$new->statement('SET FOREIGN_KEY_CHECKS=0');
foreach ($old->select("select name from sqlite_master where type='table' and name not like 'sqlite_%'") as $t) {
  $t = $t->name;
  if (in_array($t, ['migrations','sessions','cache','cache_locks'])) continue;
  if (!Schema::connection(config('database.default'))->hasTable($t)) { echo "skip $t\n"; continue; }
  $new->table($t)->truncate();
  $n = 0;
  $old->table($t)->orderByRaw('rowid')->chunk(200, function($rows) use ($new,$t,&$n){
    $new->table($t)->insert($rows->map(fn($r)=>(array)$r)->all()); $n += count($rows);
  });
  echo "$t: $n rows\n";
}
$new->statement('SET FOREIGN_KEY_CHECKS=1');
echo "DONE\n";
PHPEOF

./deploy.sh

scp -P $PORT database/database.sqlite $SSH_USER@$HOST:~/campulsy/database/old.sqlite
scp -P $PORT copy-sqlite.php $SSH_USER@$HOST:~/campulsy/
ssh -p $PORT $SSH_USER@$HOST "cd ~/campulsy && $PHPBIN copy-sqlite.php"
rsync -az -e "ssh -p $PORT" storage/app/public/ $SSH_USER@$HOST:~/campulsy/storage/app/public/
ssh -p $PORT $SSH_USER@$HOST "rm ~/campulsy/database/old.sqlite ~/campulsy/copy-sqlite.php; sed -i 's/APP_NAME=.*/APP_NAME=Naurvex/' ~/campulsy/.env; cd ~/campulsy && $PHPBIN artisan optimize:clear && $PHPBIN artisan optimize"

echo "=========== OVERVIEW BUTTONS CHECK ==========="
grep -nE "airtime|data|electric|cable|overflow|hidden|slice|take|@foreach|grid-cols|flex-nowrap" resources/views/overview.blade.php | head -40
echo "----------- controller -----------"
grep -nE "take|limit|slice|services" app/Http/Controllers/OverviewController.php
echo "=========== ALL DONE ==========="
