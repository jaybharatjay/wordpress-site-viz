<?php
/*
	We are using 1 function in this page
    This page is related: It will return the particular comment id.
    This page will call: In pop method if any comment is deleted
    This page will call automatically by this way: Through pop call via index2.php using pubnub api
    This page wil call manulaly by this way: No way
    Functions are: 
	function getPosts($pubnub,$whichPost)
*/
ini_set("display_errors", 1);
require_once('lib/autoloader.php');
use Pubnub\Pubnub;
$pubnub = new Pubnub('pub-c-3e92490d-3935-49d6-a2f1-f7f935f88036', 'sub-c-6c450ff2-3ae9-11e5-8579-02ee2ddab7fe');
$data = getPosts($pubnub,$whichComment);
function getPosts($pubnub,$whichComment){
    if($whichComment){
       $data = '';
            $data.="{".
                '"comment_id":'. '"'.$whichComment.'"';
        $data='{'.'"result":'.'"Yes",'.'"action":'.'"Delete",'.'"records"'.':['.$data.'}]}';
        $publish_result = $pubnub->publish('demojay',$data);
    }else{
    }
}
?>