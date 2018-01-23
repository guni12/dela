<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\Taglinks;
use \Guni\Comments\HTMLForm\CreateCommForm;
use \Guni\Comments\HTMLForm\Form;

use \Guni\Comments\ShowOneService;

/**
 * HTML Form elements.
 */
class CommentUsageTest extends \PHPUnit_Framework_TestCase
{
    public $di;

    /**
     * Setup before each testcase
     */
    public function setUp()
    {
        $this->di = new \Anax\DI\DIFactoryConfig("di.php");
    }

    

    public function testTags()
    {
        $tags = new Taglinks($this->di);
        $this->assertInstanceOf("\Guni\Comments\Taglinks", $tags);
        $res = $tags->setUrlCreator("comm");
        $res2 = $tags->getHTML();
        $test2 = '<table class = "member"><tr><th class = "elcar"><a href = "://.bin/comm/tags/elcar">Elbil</a></th><th class = "safety"><a href = "://.bin/comm/tags/safety">Säkerhet</a></th><th class = "light"><a href = "://.bin/comm/tags/light">Belysning</a></th><th class = "heat"><a href = "://.bin/comm/tags/heat">Värme</a></th></tr><tr><td>Text om elbilar</td><td>Text om Säkerhet</td><td>Text om belysning</td><td>Text om Värme</td></tr></table><br /><br />Saknar du någon tagg? Hör av dig till admin.';
        //$this->assertContains(4, [1, 2, 3]);
        $test = "://.bin/comm";
        $this->assertEquals($test, $res);
        $this->assertEquals($test2, $res2);
    }


    public function testComm()
    {
        $comm = new Comm($this->di);
        $this->assertInstanceOf("\Guni\Comments\Comm", $comm);
        $email = "gunvor@behovsbo.se";
        $test = "https://www.gravatar.com/avatar/2438dc720f1ca2c32c27a5bb658229c4?s=20&d=mm&r=g";
        $res = $comm->getGravatar($email);
        $this->assertEquals($res, $test);
    }


    public function testCreateForm()
    {
        $create = new CreateCommForm($this->di, null);
        $this->assertInstanceOf("\Guni\Comments\HTMLForm\CreateCommForm", $create);
        $array = ["elcar", "heat"];
        $test = $create->handleTags($array);
        $exp = Array (0 => 'elcar', 1 => null, 2 => null, 3 => 'heat');
        $exp2 = Array ('type' => 'hidden', 'value' => 'answer');
        $exp3 = Array (0 => 'elcar', 1 => null, 2 => null, 3 => null);
        $exp4 =  Array ('type' => 'select-multiple', 'label' => 'Taggar, minst en:', 'description' => 'Håll ner Ctrl (windows) / Command (Mac) knapp för att välja flera taggar.<br />Default tagg är Elbil.', 'size' => 5, 'options' => Array ("elcar" => "elbil", "safety" => "säkerhet", "light"  => "belysning", "heat"   => "värme"));

        $this->assertEquals($exp, $test);

        $comment = $create->notQuestion();
        $this->assertEquals($exp2, $comment);

        $tagdefault = $create->tagsToArray("");
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
        $form = new Form($this->di);
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
}
