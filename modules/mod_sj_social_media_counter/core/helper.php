<?php
/**
 * @package SJ Social Media Counter
 * @version 1.0.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2014 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */

defined('_JEXEC') or die ();

abstract class SjSocialMediaCountsHelper
{
    public static function getList(&$params)
    {
        $return = array();
        if ((int)$params->get('display_sfacebook', 0)) {
            $return['count_facebook_like'] = self::getFacebookLike($params);
        }

        if ((int)$params->get('display_stwitter', 0)) {
            $return['count_followers_twitter'] = self::getFollowersTwitter($params);
        }

        if ((int)$params->get('display_slinkedin', 0)) {
            $return['count_followers_linkedin'] = self::getFollowersLinkedin($params);
        }

        if ((int)$params->get('display_svimeo', 0)) {
            $return['count_followers_vimeo'] = self::getFollowersVimeo($params);
        }

        if ((int)$params->get('display_ssoundcloud', 0)) {
            $return['count_followers_soundcloud'] = self::getFollowersSoundCloud($params);
        }

        if ((int)$params->get('display_sdribbble', 0)) {
            $return['count_followers_dribbble'] = self::getFollowersDribbble($params);
        }

        if ((int)$params->get('display_syoutube', 0)) {
            $return['count_subscribers_youtube'] = self::getSubscribersYoutube($params);
        }

        if ((int)$params->get('display_sgplus', 0)) {
            $return['count_followers_gplus'] = self::getFollowersGplus($params);
        }

        if ((int)$params->get('display_sinstagram', 0)) {
            $return['count_followers_instagram'] = self::getFollowersInstagram($params);
        }

        if ((int)$params->get('display_rss', 0)) {
            $return['rss_url'] = $params->get('rss_url', '#');
        }
        return $return;
    }

    // Facebook //
    private static function getFacebookLike($params)
    {
        $url = $params->get('facebook_url');
        $url = urlencode($url);
        $json_string = @file_get_contents('http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls=' . $url);
        $json = json_decode($json_string, true);
        $like_count = isset($json['0']) && $json['0']['like_count'] ? $json['0']['like_count'] : 0;
        return $like_count;

    }

    // Twitter //
    private static function getFollowersTwitter($params)
    {
        if (!class_exists('TwitterOAuth')) {
            require_once dirname(__FILE__) . '/twitteroauth.php';
        }
        $consumerKey = $params->get('consumekey');
        $consumerSecret = $params->get('consumersecret');
        $oAuthToken = trim($params->get('access_token'));
        $oAuthSecret = trim($params->get('access_token_secret'));
        $screenName = $params->get('screenname');
        $Tweet = new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);
        $followers = $Tweet->get('users/show', array('screen_name' => $screenName));
        $json = json_decode($followers);
        $followers_count = isset($json->followers_count) ? $json->followers_count : 0;
        return $followers_count;

    }

    // Linkedin //
    private static function getFollowersLinkedin($params)
    {
        if (!class_exists('LinkedInOAuth')) {
            require_once dirname(__FILE__) . '/LinkedInOAuth.php';
        }
        $api_config = array(
            'appKey' => $params->get('api_key'),
            'appSecret' => $params->get('api_secret'),
            'callbackUrl' => NULL
        );

        $token_config = array('oauth_token' => $params->get('oauth_token'),
            'oauth_token_secret' => $params->get('oauth_token_secret')
        );

        $linkedin = new LinkedInOAuth($api_config);
        $linkedin->setToken($token_config);
        $page_id = $params->get('linkedin_pageid');
        $response = $linkedin->getFollowersCount($page_id);
        $followers_count = 0;
        if ($response['success']) {
            $_xml = isset($response['linkedin']) ? $response['linkedin'] : '';

            if ($_xml != false && $_xml != '') {
                $_array = $linkedin->xmlToArray($_xml);
                $followers_count = isset($_array['company']['children']['num-followers']['content']) ? $_array['company']['children']['num-followers']['content'] : 0;
            }
        }
        return $followers_count;
    }

    // Vimeo //
    private static function getFollowersVimeo($params)
    {
        $url = $params->get('vimeo_username', 'royalhandmadecustoms');
        $url = urlencode($url);
        $json_string = @file_get_contents('http://vimeo.com/api/v2/' . $url . '/info.json');
        $json = json_decode($json_string, true);
        $followers_count = isset($json['total_contacts']) ? $json['total_contacts'] : 0;
        return $followers_count;
    }

    // SoundCloud //
    private static function getFollowersSoundCloud($params)
    {
        $soundc_un = $params->get('soundc_un', null);
        $soundc_id = $params->get('soundc_id', null);
        $json_string = @file_get_contents('http://api.soundcloud.com/users/' . $soundc_un . '.json?client_id=' . $soundc_id);
        $json = json_decode($json_string, true);
        $followers_count = isset($json['followers_count']) ? $json['followers_count'] : 0;
        return $followers_count;
    }


    // Dirbbble //
    private static function getFollowersDribbble($params)
    {
        $drbl_un = $params->get('drbl_un', 'Facebook');
        $json_string = @file_get_contents('http://api.dribbble.com/players/' . $drbl_un);
        $json = json_decode($json_string, true);
        $followers_count = isset($json['followers_count']) ? $json['followers_count'] : 0;
        return $followers_count;

    }

    // Subscribers Youtube //
    private static function getSubscribersYoutube($params)
    {
        $yt_channel = $params->get('yt_channel');
        $json_string = @file_get_contents('http://gdata.youtube.com/feeds/api/users/' . $yt_channel . '?alt=json');
        $json = json_decode($json_string, true);
        $followers_count = isset($json['entry']['yt$statistics']['subscriberCount']) ? $json['entry']['yt$statistics']['subscriberCount'] : 0;
        return $followers_count;
    }

    // Google Plus //
    private static function getFollowersGplus($params)
    {
        $gplus_id = $params->get('gplus_id');
        $gplus_key = $params->get('gplus_key');
        $json_string = @file_get_contents('https://www.googleapis.com/plus/v1/people/' . $gplus_id . '?key=' . $gplus_key);
        $json = json_decode($json_string, true);
        $followers_count = isset($json['circledByCount']) ? $json['circledByCount'] : 0;
        return $followers_count;
    }

    // Instagram //
    private static function  getFollowersInstagram($params)
    {
        $userID = $params->get('inst_userid', null);
        $access_token = $params->get('inst_access_token', null);
        $json_string = @file_get_contents('https://api.instagram.com/v1/users/' . $userID . '?access_token=' . $access_token);
        $json = json_decode($json_string, true);
        $followers_count = isset($json['data']['counts']['followed_by']) ? $json['data']['counts']['followed_by'] : 0;
        return $followers_count;
    }
}


