<?php

include 'stringstream.php';
include 'POParser.php';

$easy_file =<<<FILE
#: standard input:116 input:1115 input:1205
#: input:1838 input:1868 
msgid "Password"
msgstr "Mot de passe:"

#: standard input:3299
msgid "Hard id"
"foo bar\\n"
"a \" quote"
msgstr ""

#: standard input:116 input:1115 input:1205
#: input:1838 input:1868 
#~ msgid "Password"
#~ "xxx"
#~ msgstr "Mot de passe:"
#~ "xxx"

FILE;

StringStreamController::createRef('reference',$easy_file);
$fh = fopen('string://reference','r');
$store = new TempPoMsgStore();

$t = new Tester();
$parser = new POParser($store);
$parser->parseEntriesFromStream($fh);

$objects = $store->read();

$t->assertTrue($objects[1]);
$t->assertTrue($objects[0]);
$t->assertFalse($objects[10]);
$t->assertTrue($objects[0]['msgid']);
$t->assertTrue($objects[0]['msgstr']);
$t->assertFalse($objects[0]['should_not_exist']);
$t->assertEquals($objects[0]['msgid'],'Password');
$t->assertEquals($objects[0]['msgstr'],'Mot de passe:');
$t->assertEquals($objects[1]['msgid'],"Hard idfoo bar\na \" quote");
$t->assertEquals($objects[2]['msgid'],'Passwordxxx');
$t->assertEquals($objects[2]['msgstr'],'Mot de passe:xxx');
$t->assertTrue($objects[2]['is_obsolete']);
$t1 = "dog";
$t2 = "foo\nbar";
$t2 = 'foo\"b\na\\r';

$t->assertEquals($parser->encodeStringFormat('dog') ,'"dog"');
$t->assertEquals($parser->encodeStringFormat("dog\ncat"),"\"\"\n\"dog\\n\"\n\"cat\"");
//$t->assertEquals($parser->decodeStringFormat( $parser->encodeStringFormat($t2) ),$t2);
//$t->assertEquals($parser->decodeStringFormat( $parser->encodeStringFormat($t3) ),$t3);

var_dump($objects);

$t->printResults();

class Tester {
	
	public $pass_count =0;
	public $fail_count =0;
	
	function assertEquals($a,$b,$message="") {
		if($a !== $b) {
			$a_str = var_export($a,true);
			$b_str = var_export($b,true);
			echo "Test Failed: ($a_str not equal to $b_str) $message\n";
			$this->fail_count++;
		} else {
			$this->pass_count++;
		}
	}
	function assertTrue($a,$message="") {
		if(!$a) {
			$a_str = var_export($a,true);
			echo "Test Failed: ($a_str not TRUE): $message\n";
			$this->fail_count++;
		} else {
			$this->pass_count++;
		}
	}
	function assertFalse($a,$message="") {
		if(!!$a) {
			$a_str = var_export($a,true);
			echo "Test Failed: ($a_str not FALSE): $message\n";
			$this->fail_count++;
		} else {
			$this->pass_count++;
		}
	}
	function printResults() {
		printf("Assertions Passed: %d\nAssertions Failed: %d\n",$this->pass_count,$this->fail_count);
	}
}