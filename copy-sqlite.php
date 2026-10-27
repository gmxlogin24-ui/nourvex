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
