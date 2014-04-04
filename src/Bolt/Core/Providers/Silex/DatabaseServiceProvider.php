<?php

namespace Bolt\Core\Providers\Silex;

use PDOException;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\Provider\DoctrineServiceProvider;

use Illuminate\Support\Str;

use Symfony\Component\Config\Loader\DelegatingLoader;

class DatabaseServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $databaseConfig = $this->getDatabaseConfig($app);

        $app->register(new DoctrineServiceProvider(), array(
            'db.options' => $databaseConfig
        ));

        // Do a dummy query, to check for a proper connection to the database.
        try {
            $app['db']->query("SELECT 1;");
        } catch (PDOException $e) {
            $error = "Bolt could not connect to the database. Make sure the database is configured correctly in
                    <code>app/config/config.yml</code>, that the database engine is running.";
            if ($databaseConfig['driver'] != 'pdo_sqlite') {
                $error .= "<br><br>Since you're using " . $databaseConfig['driver'] . ", you should also make sure that the
                database <code>" . $databaseConfig['dbname'] . "</code> exists, and the configured user has access to it.";
            }
            // $checker = new \LowlevelChecks();
            // $checker->lowLevelError($error);
        }

        if ($databaseConfig['driver'] == 'pdo_sqlite') {
            $app['db']->query('PRAGMA synchronous = OFF');
        } elseif ($databaseConfig['driver'] == 'pdo_mysql') {
            /**
             * @link https://groups.google.com/forum/?fromgroups=#!topic/silex-php/AR3lpouqsgs
             */
            $app['db']->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            // set utf8 on names and connection as all tables has this charset

            $app['db']->query("SET NAMES 'utf8';");
            $app['db']->query("SET CHARACTER SET 'utf8';");
            $app['db']->query("SET CHARACTER_SET_CONNECTION = 'utf8';");
        }
    }

    public function boot(Application $app)
    {
    }

    protected function getDatabaseConfig($app)
    {
        $config = $app['config']->get('app/database');

        if (isset($config['driver']) && in_array($config['driver'], array('pdo_sqlite', 'sqlite'))) {
            $basename = isset($config['databasename']) ? basename($config['databasename']) : 'bolt';
            if ( ! Str::endsWith($basename, '.db')) {
                $basename .= '.db';
            }

            $options = array(
                'driver' => 'pdo_sqlite',
                'path' => $app['paths.databases'] . $basename,
                'randomfunction' => 'RANDOM()'
            );
        } else {
            // Assume we configured it correctly. Yeehaa!

            if (empty($config['password'])) {
                $config['password'] = '';
            }

            $driver = (isset($config['driver']) ? $config['driver'] : 'pdo_mysql');
            $randomfunction = '';
            if (in_array($driver, array('mysql', 'mysqli'))) {
                $driver = 'pdo_mysql';
                $randomfunction = 'RAND()';
            }
            if (in_array($driver, array('postgres', 'postgresql'))) {
                $driver = 'pdo_pgsql';
                $randomfunction = 'RANDOM()';
            }

            $options = array(
                'driver'         => $driver,
                'host'           => (isset($config['host']) ? $config['host'] : 'localhost'),
                'dbname'         => $config['databasename'],
                'user'           => $config['username'],
                'password'       => $config['password'],
                'randomfunction' => $randomfunction
            );

            $options['charset'] = isset($config['charset'])
                ? $config['charset']
                : 'utf8';
        }

        switch ($options['driver']) {
            case 'pdo_mysql':
                $options['port'] = isset($config['port']) ? $config['port'] : '3306';
                $options['reservedwords'] = explode(
                    ',',
                    'accessible,add,all,alter,analyze,and,as,asc,asensitive,before,between,' .
                    'bigint,binary,blob,both,by,call,cascade,case,change,char,character,check,collate,column,condition,constraint,' .
                    'continue,convert,create,cross,current_date,current_time,current_timestamp,current_user,cursor,database,databases,' .
                    'day_hour,day_microsecond,day_minute,day_second,dec,decimal,declare,default,delayed,delete,desc,describe,' .
                    'deterministic,distinct,distinctrow,div,double,drop,dual,each,else,elseif,enclosed,escaped,exists,exit,explain,' .
                    'false,fetch,float,float4,float8,for,force,foreign,from,fulltext,get,grant,group,having,high_priority,hour_microsecond,' .
                    'hour_minute,hour_second,if,ignore,in,index,infile,inner,inout,insensitive,insert,int,int1,int2,int3,int4,int8,' .
                    'integer,interval,into,io_after_gtids,io_before_gtids,is,iterate,join,key,keys,kill,leading,leave,left,like,limit,' .
                    'linear,lines,load,localtime,localtimestamp,lock,long,longblob,longtext,loop,low_priority,master_bind,' .
                    'master_ssl_verify_server_cert,match,maxvalue,mediumblob,mediumint,mediumtext,middleint,minute_microsecond,' .
                    'minute_second,mod,modifies,natural,nonblocking,not,no_write_to_binlog,null,numeric,on,optimize,option,optionally,' .
                    'or,order,out,outer,outfile,partition,precision,primary,procedure,purge,range,read,reads,read_write,real,references,' .
                    'regexp,release,rename,repeat,replace,require,resignal,restrict,return,revoke,right,rlike,schema,schemas,' .
                    'second_microsecond,select,sensitive,separator,set,show,signal,smallint,spatial,specific,sql,sqlexception,sqlstate,' .
                    'sqlwarning,sql_big_result,sql_calc_found_rows,sql_small_result,ssl,starting,straight_join,table,terminated,then,' .
                    'tinyblob,tinyint,tinytext,to,trailing,trigger,true,undo,union,unique,unlock,unsigned,update,usage,use,using,utc_date,' .
                    'utc_time,utc_timestamp,values,varbinary,varchar,varcharacter,varying,when,where,while,with,write,xor,year_month,' .
                    'zerofill,nonblocking'
                );
                break;
            case 'pdo_sqlite':
                $options['reservedwords'] = explode(
                    ',',
                    'abort,action,add,after,all,alter,analyze,and,as,asc,attach,autoincrement,' .
                    'before,begin,between,by,cascade,case,cast,check,collate,column,commit,conflict,constraint,create,cross,current_date,' .
                    'current_time,current_timestamp,database,default,deferrable,deferred,delete,desc,detach,distinct,drop,each,else,end,' .
                    'escape,except,exclusive,exists,explain,fail,for,foreign,from,full,glob,group,having,if,ignore,immediate,in,index,' .
                    'indexed,initially,inner,insert,instead,intersect,into,is,isnull,join,key,left,like,limit,match,natural,no,not,' .
                    'notnull,null,of,offset,on,or,order,outer,plan,pragma,primary,query,raise,references,regexp,reindex,release,rename,' .
                    'replace,restrict,right,rollback'
                );
                break;
            case 'pdo_pgsql':
                $options['port'] = isset($config['port']) ? $config['port'] : '5432';
                $options['reservedwords'] = explode(
                    ',',
                    'all,analyse,analyze,and,any,as,asc,authorization,between,bigint,binary,bit,' .
                    'boolean,both,case,cast,char,character,check,coalesce,collate,column,constraint,convert,create,cross,current_date,' .
                    'current_time,current_timestamp,current_user,dec,decimal,default,deferrable,desc,distinct,do,else,end,except,exists,' .
                    'extract,float,for,foreign,freeze,from,full,grant,group,having,ilike,in,initially,inner,int,integer,intersect,interval,' .
                    'into,is,isnull,join,leading,left,like,limit,localtime,localtimestamp,natural,nchar,new,none,not,notnull,null,nullif,' .
                    'numeric,off,offset,old,on,only,or,order,outer,overlaps,overlay,placing,position,primary,real,references,right,row,' .
                    'select,session_user,setof,similar,smallint,some,substring,table,then,time,timestamp,to,trailing,treat,trim,union,' .
                    'unique,user,using,varchar,verbose,when,where,false,true'
                );
        }

        return $options;
    }
}
