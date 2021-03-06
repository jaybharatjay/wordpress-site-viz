<?php
/*
	We are using 3 function in this page
    This page is related: It will save the particular comment id.
    This page will call: In pop method if any comment is added
    This page will call automatically by this way: Through pop call via index2.php using pubnub api
	This is dependent to: pubnub libraray to save comments via pop technology
    This page wil call manulaly by this way: No way
    Functions are: 
	function getPosts($pubnub,$whichPost)
	function cleanObject($object)
	
	function getSentiment($comment_id)
*/
ini_set("display_errors", 1);
require_once('classCommon.php');
$common = new common();
require_once('lib/autoloader.php');
use Pubnub\Pubnub;
require_once('commonFunctions.php');
$arraySettings = getSettings();
$pubnub_subs_key = $arraySettings[0]->pubnub_subs_key;
$pubnub_pub_key = $arraySettings[0]->pubnub_pub_key;
$pubnub_chanel_name = $arraySettings[0]->pubnub_chanel_name;
$pubnub = new Pubnub($pubnub_pub_key, $pubnub_subs_key);

$data = getPosts($pubnub,$whichComment,$pubnub_chanel_name,$common);
function getPosts($pubnub,$whichComment,$pubnub_chanel_name,$common){
    global $wpdb;  
    $queryAllComments = "SELECT
     viz_comments.posts_id,  viz_comments.comment_id,  viz_comments.comment_content,viz_comments.comment_author,viz_comments.comment_author_email,viz_comments.comment_date,viz_comments.comment_date_gmt,viz_comments.comment_approved FROM viz_comments 
WHERE 
viz_comments.comment_id = $whichComment
LIMIT 0,1
";
    $comments = $wpdb->get_results($queryAllComments);
    if($comments){
        $data = '';
        $data="{".
            
            '"Type":'. '"Comments",'.
            '"commentsAndMeta":'. '{';
            $commentsJson = '';                
            $commentsFoundForThisPost = false;
            if($comments){
                $commentsFoundForThisPost = true;
                $comment_post_id = $comments[0]->posts_id;
                $comment_id = $comments[0]->comment_id;
                list($neg,$neutral,$pos,$label) = $common->getSentiment($comment_id);

                $comment_content = $comments[0]->comment_content;
                $comment_id = $comments[0]->comment_id;
                $comment_content = $common->cleanObject($comment_content);
                $comment_author = $comments[0]->comment_author;
                $comment_author_email = $comments[0]->comment_author_email;
                $comment_date = $comments[0]->comment_date;
                $comment_date_gmt = $comments[0]->comment_date_gmt;
                $comment_approved = $comments[0]->comment_approved;
                $commentsJson= '{'.
                       '"comment_post_id":'. '"'.$comment_post_id.'",'.
                       '"comment_id":'. '"'.$comment_id.'",'.
                       '"comment_content":'. '"'.$comment_content.'",'.
                       '"comment_author":'. '"'.$comment_author.'",'.
                       '"comment_author_email":'. '"'.$comment_author_email.'",'.
                       '"comment_date":'. '"'.$comment_date.'",'.
                       '"comment_date_gmt":'. '"'.$comment_date_gmt.'",'.
                       '"comment_approved":'. '"'.$comment_approved.'",'.
                       '"neg":'. '"'.$neg.'",'.
                       '"neutral":'. '"'.$neutral.'",'.
                       '"pos":'. '"'.$pos.'",'.
                       '"label":'. '"'.$label.'"'.
                       
                    '}';
            }
            if(count($comments)>1){
                $commentsJson=substr($commentsJson,0,-1); 
            }
            if($commentsFoundForThisPost){
                 $data.='"comment":'.'['.$commentsJson.']';
            }
            $data.='}'.
            '}';        
             $data='{'.'"result":'.'"Yes",'.'"records"'.':['.$data.']}';
             $publish_result = $pubnub->publish($pubnub_chanel_name,$data);
             
             
             //now calling a function for update total comment count in viz_posts
             $result = updateCommentCount($comment_post_id);
             
             
    }else{
    }
}

function updateCommentCount($comment_post_id){
    //get total commennt for particular post
    global $wpdb;  
    $queryAllComments = "SELECT COUNT(id) AS total FROM `viz_comments` WHERE posts_id=$comment_post_id";
    $comments = $wpdb->get_results($queryAllComments);
    $total = 0;
    if($comments){
        $total = $comments[0]->total;
    }
    if($total){
        //echo $total;
        //update comment_count in viz_posts table
        //update
		  global $wpdb;   
          $table = "viz_posts";
          $data_array = array(
            'comment_count' => $total            
           );
          $where = array('posts_ID' => $comment_post_id);
          $wpdb->update( $table, $data_array, $where );
          //print_r ($data_array);
          //print_r ($where);
          //echo $wpdb->last_query;
          //echo $wpdb->last_result;
          //echo $wpdb->last_error;
          
		      //update close
    }
}
?>