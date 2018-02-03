<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\Misc;
use \Guni\Comments\Taglinks;
use \Guni\Comments\HTMLForm\CreateCommForm;
use \Guni\Comments\HTMLForm\Form;

use \Guni\User\User;
use \Guni\User\UserHelp;

/**
 * HTML Form elements.
 */
class CommentUsageTest extends \PHPUnit_Framework_TestCase
{
    public static $di;
    public static $db;
    public static $sess;
    public $misc;
    public $when;
    public $person;

    /**
     * Setup before each testcase
     */
    public function setUp()
    {
        self::$di = new \Anax\DI\DIFactoryConfig(__DIR__ . "/../di_dummy.php");
        self::$sess = self::$di->get("session");
        $this->misc = new Misc(self::$di);
        $this->when = null;
        $this->person = null;
    }

    

    public function testTags()
    {
        $tags = new Taglinks(self::$di);
        $this->assertInstanceOf("\Guni\Comments\Taglinks", $tags);
        $res = $this->misc->setUrlCreator("comm");
        $res2 = $tags->getHTML();
        $test2 = '<table class = "member"><tr><th class = "elcar"><a href = "://.bin/comm/tags/elcar">Elbil</a></th><th class = "safety"><a href = "://.bin/comm/tags/safety">Säkerhet</a></th><th class = "light"><a href = "://.bin/comm/tags/light">Belysning</a></th><th class = "heat"><a href = "://.bin/comm/tags/heat">Värme</a></th></tr><tr><td>Text om elbilar</td><td>Text om Säkerhet</td><td>Text om belysning</td><td>Text om Värme</td></tr></table><br /><br />Saknar du någon tagg? Hör av dig till admin.';
        //$this->assertContains(4, [1, 2, 3]);
        $test = "://.bin/comm";


        $this->assertEquals($test, $res);
        $this->assertEquals($test2, $res2);
    }


    public function testComm()
    {
        $comm = new Comm(self::$di);
        $this->assertInstanceOf("\Guni\Comments\Comm", $comm);
        $email = "gunvor@behovsbo.se";
        $test = "https://www.gravatar.com/avatar/2438dc720f1ca2c32c27a5bb658229c4?s=20&d=mm&r=g";
        $res = $comm->getGravatar($email);
        $this->assertEquals($res, $test);
    }


    public function testCreateForm()
    {
        $create = new CreateCommForm(self::$di, null);
        $this->assertInstanceOf("\Guni\Comments\HTMLForm\CreateCommForm", $create);
        $array = ["elcar", "heat"];
        $test = $create->handleTags($array);
        $exp = array (0 => 'elcar', 1 => null, 2 => null, 3 => 'heat');
        $exp2 = array ('type' => 'hidden', 'value' => 'answer');
        $exp3 = array (0 => 'elcar', 1 => null, 2 => null, 3 => null);
        $exp4 =  array ('type' => 'select-multiple', 'label' => 'Taggar, minst en:', 'description' => 'Håll ner Ctrl (windows) / Command (Mac) knapp för att välja flera taggar.<br />Default tagg är Elbil.', 'size' => 5, 'options' => array ("elcar" => "elbil", "safety" => "säkerhet", "light"  => "belysning", "heat"   => "värme"));

        $this->assertEquals($exp, $test);

        $comment = $create->notQuestion();
        $this->assertEquals($exp2, $comment);

        $tagdefault = $create->tagsToArray([]);
        $this->assertEquals($exp3, $tagdefault);

        $dropdownSelection = $create->getDropdown(0);
        $this->assertEquals($exp4, $dropdownSelection);

        $formtest = $create->aForm(null, null);
        $this->assertEquals($formtest, null);

    }


    /**
     * Test
     */
    public function testCreate1()
    {
        $form = new Form(self::$di);
        $this->assertInstanceOf("\Guni\Comments\HTMLForm\Form", $form);
        $form->create();

        $res = $form->getHTML();
        $exp = <<<EOD
\n<div id='wmd-button-bar'></div><form id='anax/htmlform' class='htmlform' method='post'>
<input type="hidden" name="anax/htmlform-id" value="anax/htmlform" />
<fieldset>



</fieldset>
</form>

<div id="wmd-preview" class="wmd-panel wmd-preview"></div>
EOD;


        $this->assertEquals($exp, $res, "Empty form missmatch.");
    }


    /**
     * Test db connection with sqlite::memory
     */
    public function testReachDb()
    {
        self::$db = self::$di->get("db");
        self::$db->connect();

        self::$db->createTable(
            "comm",
            [
                "id" => ["integer", "primary key", "not null"],
                "userid" => ["VARCHAR"],
                "title" => ["VARCHAR"],
                "comment" => ["VARCHAR"],
                "parentid" => ["VARCHAR"],
                "iscomment" => ["VARCHAR"],
                "points" => ["VARCHAR"],
                "hasvoted" => ["VARCHAR"],
                "accept" => ["VARCHAR"],
                "created" => ["VARCHAR"],
                "updated" => ["DATETIME"],
            ]
        )->execute();
    }


    /**
    *
    * Test insert in db-memory-file
    */
    public function testInsert()
    {
        $create = new CreateCommForm(self::$di, null);

        $textfilter = self::$di->get("textfilter");
        $parses = ["yamlfrontmatter", "shortcode", "markdown", "titlefromheader"];
        $text = $textfilter->parse("#Rubrik#\n__Brödtext__", $parses);
        $text->frontmatter['title'] = "Titel";
        $text->frontmatter['tags'] = $create->handleTags("");
        $text = json_encode($text);

        $now = date("Y-m-d H:i:s");
        $comm = new Comm();
        $comm->setDb(self::$db);


        $comm->title = "Titel";
        $comm->userid = 1;
        $comm->parentid = null;
        $comm->iscomment = null;
        $comm->comment = $text;
        $comm->created = $now;
        $comm->save();

        $findIt = new Comm(self::$di);
        $findIt->setDb(self::$db);
        $comment = $findIt->find("userid", 1);
        $this->assertEquals($comm->comment, $comment->comment);
        $this->assertEquals($comm->created, $comment->created);

        $updated = $this->misc->isUpdated($findIt);
        $exp = 'Fråga: ' . $now . ', Ändrad: ';
        $this->assertEquals($updated, $exp);

        $notupdated = $this->misc->isNotUpdated($findIt);
        $exp = 'Fråga: ' . $now;
        $this->assertEquals($notupdated, $exp);

        $this->when = $this->misc->getWhen($findIt);
        $exp2 = $exp;
        $this->assertEquals($this->when, $exp);
    }


    /**
    *
    * Test the form that don't init with db
    */
    public function testNotQuestion()
    {
        $create = new CreateCommForm(self::$di, null);
        $res = $create->getDropdown(1);
        $exp = array ('type' => 'hidden', 'value' => 'answer');
        $this->assertEquals($res, $exp);
    }


    /**
    *
    * Create a User
    */
    public function makeUser()
    {
        self::$db = self::$di->get("db");
        self::$db->connect();

        self::$db->createTable(
            "user",
            [
                "id" => ["INTEGER"],
                "acronym" => ["VARCHAR"],
                "password" => ["VARCHAR"],
                "email" => ["VARCHAR"],
                "profile" => ["VARCHAR"],
                "isadmin" => ["INTEGER"],
                "created" => ["DATETIME"],
                "updated" => ["DATETIME"],
                "deleted" => ["DATETIME"],
            ]
        )->execute();

        $create = new CreateUserForm(self::$di, null);
        $now = date("Y-m-d H:i:s");

        $user = new User();
        $user->setDb(self::$db);
        $user->acronym = 'guni';
        $user->email = 'gunvor@behovsbo.se';
        $user->profile = 'Kuddkulla';
        $user->setPassword('root');
        $user->created = $now;
        $user->save();

        $findPerson = new User(self::$di);
        $findPerson->setDb(self::$db);
        $compare = $findPerson->find("acronym", 'guni');
        $this->person = $findPerson->find("profile", 'Kuddkulla');

        $this->assertEquals($user->acronym, $compare->acronym);
        $this->assertEquals($user->profile, $compare->profile);
    }



    /**
    *
    * Test various small functions
    */
    public function testMisc()
    {
        $userhelp = new UserHelp(self::$di);

        $text = $this->misc->getUsersHtml($this->person, "view");
        $exp = array ('acronym' => '<a href="://.bin/user/view-one/"></a>','gravatar' => '<a href="://.bin/user/view-one/"><img src="https://www.gravatar.com/avatar/d41d8cd98f00b204e9800998ecf8427e?s=20&d=mm&r=g" alt=""/></a>');



        $this->assertEquals($text, $exp);


        $findIt = new Comm(self::$di);
        $findIt->setDb(self::$db);
        $test = $findIt->find("userid", 1);

        $text2 = $this->misc->getTheText($test, [2,4,6], $this->when, $this->person, 1);
        $exp = '<tr><td class = "allmember"><img src="https://www.gravatar.com/avatar/d41d8cd98f00b204e9800998ecf8427e?s=20&d=mm&r=g" alt=""/> </td><td class = "alltitle"><a href="://.bin/comm/view-one/1"> Titel</a></td><td class = "asked"></td><td = "respons"><span class = "smaller">246</span></td></tr>';
        $this->assertEquals($text2, $exp);
    }
}
