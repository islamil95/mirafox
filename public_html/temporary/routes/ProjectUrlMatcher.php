<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class ProjectUrlMatcher extends Symfony\Component\Routing\Matcher\UrlMatcher
{
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($rawPathinfo)
    {
        $allow = [];
        $pathinfo = rawurldecode($rawPathinfo);
        $trimmedPathinfo = rtrim($pathinfo, '/');
        $context = $this->context;
        $request = $this->request ?: $this->createRequest($pathinfo);
        $requestMethod = $canonicalMethod = $context->getMethod();

        if ('HEAD' === $requestMethod) {
            $canonicalMethod = 'GET';
        }

        // index
        if ('/' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\Index\\IndexController',  '_route' => 'index',);
            if (!in_array($canonicalMethod, ['GET'])) {
                $allow = array_merge($allow, ['GET']);
                goto not_index;
            }

            return $ret;
        }
        not_index:

        // notfound
        if ('/notfound' === $pathinfo) {
            return array (  '_controller' => 'Controllers\\Error\\PageNotFoundController',  '_route' => 'notfound',);
        }

        if (0 === strpos($pathinfo, '/new')) {
            // new_offer
            if ('/new_offer' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Want\\Worker\\NewOfferController',  '_route' => 'new_offer',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_new_offer;
                }

                return $ret;
            }
            not_new_offer:

            if (0 === strpos($pathinfo, '/new_project')) {
                // new_project
                if ('/new_project' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\NewWantController',  '_route' => 'new_project',);
                    if (!in_array($canonicalMethod, ['GET'])) {
                        $allow = array_merge($allow, ['GET']);
                        goto not_new_project;
                    }

                    return $ret;
                }
                not_new_project:

                // new_project_form_handler
                if ('/new_project' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\NewWantHandlerController',  '_route' => 'new_project_form_handler',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_new_project_form_handler;
                    }

                    return $ret;
                }
                not_new_project_form_handler:

            }

            // kwork_add
            if ('/new' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Kwork\\AddEditKworkController',  '_route' => 'kwork_add',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_kwork_add;
                }

                return $ret;
            }
            not_kwork_add:

            // kwork_save_new
            if ('/new' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Kwork\\SaveKworkController',  '_route' => 'kwork_save_new',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_kwork_save_new;
                }

                return $ret;
            }
            not_kwork_save_new:

        }

        elseif (0 === strpos($pathinfo, '/api')) {
            if (0 === strpos($pathinfo, '/api/user')) {
                if (0 === strpos($pathinfo, '/api/user/c')) {
                    if (0 === strpos($pathinfo, '/api/user/check')) {
                        // api_user_checkemail
                        if ('/api/user/checkemail' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckEmailController',  '_route' => 'api_user_checkemail',);
                            if (!in_array($canonicalMethod, ['GET'])) {
                                $allow = array_merge($allow, ['GET']);
                                goto not_api_user_checkemail;
                            }

                            return $ret;
                        }
                        not_api_user_checkemail:

                        // api_user_checklogin
                        if ('/api/user/checklogin' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckLoginController',  '_route' => 'api_user_checklogin',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_user_checklogin;
                            }

                            return $ret;
                        }
                        not_api_user_checklogin:

                        // api_user_checkverifycode
                        if ('/api/user/checkverifycode' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckVerifyCodeController',  '_route' => 'api_user_checkverifycode',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_user_checkverifycode;
                            }

                            return $ret;
                        }
                        not_api_user_checkverifycode:

                        if (0 === strpos($pathinfo, '/api/user/checksettings')) {
                            // api_user_checksettingspasswords
                            if ('/api/user/checksettingspasswords' === $pathinfo) {
                                $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckSettingsPasswordsController',  '_route' => 'api_user_checksettingspasswords',);
                                if (!in_array($requestMethod, ['POST'])) {
                                    $allow = array_merge($allow, ['POST']);
                                    goto not_api_user_checksettingspasswords;
                                }

                                return $ret;
                            }
                            not_api_user_checksettingspasswords:

                            // api_user_checksettingspayments
                            if ('/api/user/checksettingspayments' === $pathinfo) {
                                $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckSettingsPaymentsController',  '_route' => 'api_user_checksettingspayments',);
                                if (!in_array($requestMethod, ['POST'])) {
                                    $allow = array_merge($allow, ['POST']);
                                    goto not_api_user_checksettingspayments;
                                }

                                return $ret;
                            }
                            not_api_user_checksettingspayments:

                            // api_user_checksettingsemail
                            if ('/api/user/checksettingsemail' === $pathinfo) {
                                $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckSettingsEmailController',  '_route' => 'api_user_checksettingsemail',);
                                if (!in_array($requestMethod, ['POST'])) {
                                    $allow = array_merge($allow, ['POST']);
                                    goto not_api_user_checksettingsemail;
                                }

                                return $ret;
                            }
                            not_api_user_checksettingsemail:

                            // api_user_checksettingsusername
                            if ('/api/user/checksettingsusername' === $pathinfo) {
                                $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckSettingsUsernameController',  '_route' => 'api_user_checksettingsusername',);
                                if (!in_array($requestMethod, ['POST'])) {
                                    $allow = array_merge($allow, ['POST']);
                                    goto not_api_user_checksettingsusername;
                                }

                                return $ret;
                            }
                            not_api_user_checksettingsusername:

                        }

                        // api_user_checkworkeroffers
                        if ('/api/user/checkworkeroffers' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckWorkerOffersController',  '_route' => 'api_user_checkworkeroffers',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_user_checkworkeroffers;
                            }

                            return $ret;
                        }
                        not_api_user_checkworkeroffers:

                        // api_user_checknotify
                        if ('/api/user/checknotify' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\User\\CheckNotifyController',  '_route' => 'api_user_checknotify',);
                            if (!in_array($canonicalMethod, ['GET'])) {
                                $allow = array_merge($allow, ['GET']);
                                goto not_api_user_checknotify;
                            }

                            return $ret;
                        }
                        not_api_user_checknotify:

                    }

                    // api_user_changeemail
                    if ('/api/user/changeemail' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\ChangeEmailController',  '_route' => 'api_user_changeemail',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_changeemail;
                        }

                        return $ret;
                    }
                    not_api_user_changeemail:

                    // api_user_closepollnotify
                    if ('/api/user/closepollnotify' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\ClosePollNotifyController',  '_route' => 'api_user_closepollnotify',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_closepollnotify;
                        }

                        return $ret;
                    }
                    not_api_user_closepollnotify:

                    // api_user_cancelpollrefuse
                    if ('/api/user/cancelpollrefuse' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\CancelPollRefuseController',  '_route' => 'api_user_cancelpollrefuse',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_cancelpollrefuse;
                        }

                        return $ret;
                    }
                    not_api_user_cancelpollrefuse:

                }

                // api_user_login
                if ('/api/user/login' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\User\\LoginController',  '_route' => 'api_user_login',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_user_login;
                    }

                    return $ret;
                }
                not_api_user_login:

                if (0 === strpos($pathinfo, '/api/user/s')) {
                    // api_user_signup
                    if ('/api/user/signup' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\SignUpController',  '_route' => 'api_user_signup',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_signup;
                        }

                        return $ret;
                    }
                    not_api_user_signup:

                    // api_user_simplesignup
                    if ('/api/user/simplesignup' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\SimpleSignUpController',  '_route' => 'api_user_simplesignup',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_simplesignup;
                        }

                        return $ret;
                    }
                    not_api_user_simplesignup:

                    if (0 === strpos($pathinfo, '/api/user/set')) {
                        // api_user_setlookedlesson
                        if ('/api/user/setlookedlesson' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\User\\SetLookedLessonController',  '_route' => 'api_user_setlookedlesson',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_user_setlookedlesson;
                            }

                            return $ret;
                        }
                        not_api_user_setlookedlesson:

                        // api_user_setworkerstatusswitchall
                        if ('/api/user/setworkerstatusswitchall' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\User\\SetWorkerStatusSwitchAllController',  '_route' => 'api_user_setworkerstatusswitchall',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_user_setworkerstatusswitchall;
                            }

                            return $ret;
                        }
                        not_api_user_setworkerstatusswitchall:

                        // api_user_setmessagesubmitmode
                        if ('/api/user/setmessagesubmitmode' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\User\\SetMessageSubmitModeController',  '_route' => 'api_user_setmessagesubmitmode',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_user_setmessagesubmitmode;
                            }

                            return $ret;
                        }
                        not_api_user_setmessagesubmitmode:

                    }

                    // api_user_sendphoneverifycode
                    if ('/api/user/sendphoneverifycode' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\SendPhoneVerifyCodeController',  '_route' => 'api_user_sendphoneverifycode',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_sendphoneverifycode;
                        }

                        return $ret;
                    }
                    not_api_user_sendphoneverifycode:

                    // api_user_switchworkerstatus
                    if ('/api/user/switchworkerstatus' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\SwitchWorkerStatusController',  '_route' => 'api_user_switchworkerstatus',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_switchworkerstatus;
                        }

                        return $ret;
                    }
                    not_api_user_switchworkerstatus:

                    // api_user_showrecommendationsintrackpage
                    if ('/api/user/showrecommendationsintrackpage' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\ShowRecommendationsInTrackPageController',  '_route' => 'api_user_showrecommendationsintrackpage',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_showrecommendationsintrackpage;
                        }

                        return $ret;
                    }
                    not_api_user_showrecommendationsintrackpage:

                }

                elseif (0 === strpos($pathinfo, '/api/user/get')) {
                    // api_user_getonlineusers
                    if ('/api/user/getonlineusers' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\GetOnlineUsersController',  '_route' => 'api_user_getonlineusers',);
                        if (!in_array($canonicalMethod, ['GET'])) {
                            $allow = array_merge($allow, ['GET']);
                            goto not_api_user_getonlineusers;
                        }

                        return $ret;
                    }
                    not_api_user_getonlineusers:

                    // api_user_getworkerstatushelp
                    if ('/api/user/getworkerstatushelp' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\GetWorkerStatusHelpController',  '_route' => 'api_user_getworkerstatushelp',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_user_getworkerstatushelp;
                        }

                        return $ret;
                    }
                    not_api_user_getworkerstatushelp:

                    // api_user_getusercurrencybylogin
                    if ('/api/user/getusercurrencybylogin' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\User\\GetUserCurrencyByLoginController',  '_route' => 'api_user_getusercurrencybylogin',);
                        if (!in_array($canonicalMethod, ['GET'])) {
                            $allow = array_merge($allow, ['GET']);
                            goto not_api_user_getusercurrencybylogin;
                        }

                        return $ret;
                    }
                    not_api_user_getusercurrencybylogin:

                }

                // api_user_ispaymenypayed
                if ('/api/user/ispaymentpayed' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\User\\IsPaymentPayedController',  '_route' => 'api_user_ispaymenypayed',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_user_ispaymenypayed;
                    }

                    return $ret;
                }
                not_api_user_ispaymenypayed:

                // api_user_addreview
                if ('/api/user/addreview' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\User\\AddReviewController',  '_route' => 'api_user_addreview',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_user_addreview;
                    }

                    return $ret;
                }
                not_api_user_addreview:

                // api_user_hidetechnicalworksnotification
                if ('/api/user/hidetechnicalworksnotification' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\User\\HideTechnicalWorksNotificationController',  '_route' => 'api_user_hidetechnicalworksnotification',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_user_hidetechnicalworksnotification;
                    }

                    return $ret;
                }
                not_api_user_hidetechnicalworksnotification:

            }

            // api_ban_disallowfreeregister
            if ('/api/ban/disallowfreeregister' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Api\\Ban\\DisallowFreeRegisterController',  '_route' => 'api_ban_disallowfreeregister',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_api_ban_disallowfreeregister;
                }

                return $ret;
            }
            not_api_ban_disallowfreeregister:

            if (0 === strpos($pathinfo, '/api/order')) {
                // api_order_create
                if ('/api/order/create' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Order\\CreateController',  '_route' => 'api_order_create',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_order_create;
                    }

                    return $ret;
                }
                not_api_order_create:

                // api_order_getorderprovideddata
                if ('/api/order/getorderprovideddata' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Order\\GetOrderProvidedDataController',  '_route' => 'api_order_getorderprovideddata',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_order_getorderprovideddata;
                    }

                    return $ret;
                }
                not_api_order_getorderprovideddata:

                // api_order_getorderscount
                if ('/api/order/getorderscount' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Order\\GetOrdersCountController',  '_route' => 'api_order_getorderscount',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_order_getorderscount;
                    }

                    return $ret;
                }
                not_api_order_getorderscount:

            }

            elseif (0 === strpos($pathinfo, '/api/offer')) {
                // api_offer_deleteoffer
                if ('/api/offer/deleteoffer' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Offer\\DeleteOfferController',  '_route' => 'api_offer_deleteoffer',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_offer_deleteoffer;
                    }

                    return $ret;
                }
                not_api_offer_deleteoffer:

                // api_offer_addview
                if ('/api/offer/addview' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Offer\\AddViewController',  '_route' => 'api_offer_addview',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_offer_addview;
                    }

                    return $ret;
                }
                not_api_offer_addview:

                // api_offer_createoffer
                if ('/api/offer/createoffer' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Offer\\CreateOfferController',  '_route' => 'api_offer_createoffer',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_offer_createoffer;
                    }

                    return $ret;
                }
                not_api_offer_createoffer:

                // offer_editoffer
                if ('/api/offer/editoffer' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Worker\\Handler\\EditOfferHandlerController',  '_route' => 'offer_editoffer',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_offer_editoffer;
                    }

                    return $ret;
                }
                not_offer_editoffer:

                // api_offer_hideoffer
                if ('/api/offer/hideoffer' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Offer\\HideOfferController',  '_route' => 'api_offer_hideoffer',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_offer_hideoffer;
                    }

                    return $ret;
                }
                not_api_offer_hideoffer:

            }

            elseif (0 === strpos($pathinfo, '/api/kwork')) {
                if (0 === strpos($pathinfo, '/api/kwork/get')) {
                    // api_kwork_getrotation
                    if ('/api/kwork/getrotation' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\GetRotationController',  '_route' => 'api_kwork_getrotation',);
                        if (!in_array($canonicalMethod, ['GET'])) {
                            $allow = array_merge($allow, ['GET']);
                            goto not_api_kwork_getrotation;
                        }

                        return $ret;
                    }
                    not_api_kwork_getrotation:

                    // api_kwork_getrestauhorizedpopular
                    if ('/api/kwork/getrestauhorizedpopular' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\GetRestAuhorizedPopularController',  '_route' => 'api_kwork_getrestauhorizedpopular',);
                        if (!in_array($canonicalMethod, ['GET'])) {
                            $allow = array_merge($allow, ['GET']);
                            goto not_api_kwork_getrestauhorizedpopular;
                        }

                        return $ret;
                    }
                    not_api_kwork_getrestauhorizedpopular:

                    // api_kwork_getdetails
                    if ('/api/kwork/getdetails' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\GetDetailsController',  '_route' => 'api_kwork_getdetails',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_api_kwork_getdetails;
                        }

                        return $ret;
                    }
                    not_api_kwork_getdetails:

                    // api_kwork_getkworksites
                    if ('/api/kwork/getkworksites' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\GetKworkSitesController',  '_route' => 'api_kwork_getkworksites',);
                        if (!in_array($canonicalMethod, ['GET'])) {
                            $allow = array_merge($allow, ['GET']);
                            goto not_api_kwork_getkworksites;
                        }

                        return $ret;
                    }
                    not_api_kwork_getkworksites:

                    if (0 === strpos($pathinfo, '/api/kwork/getonce')) {
                        // api_kwork_getoncekworkpopular
                        if ('/api/kwork/getoncekworkpopular' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\GetOnceKworkPopularController',  '_route' => 'api_kwork_getoncekworkpopular',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_kwork_getoncekworkpopular;
                            }

                            return $ret;
                        }
                        not_api_kwork_getoncekworkpopular:

                        // api_kwork_getoncekworkbookmark
                        if ('/api/kwork/getoncekworkbookmark' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\GetOnceKworkBookmarkController',  '_route' => 'api_kwork_getoncekworkbookmark',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_kwork_getoncekworkbookmark;
                            }

                            return $ret;
                        }
                        not_api_kwork_getoncekworkbookmark:

                        // api_kwork_getonceotherkworkseller
                        if ('/api/kwork/getonceotherkworkseller' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\GetOnceOtherKworkSellerController',  '_route' => 'api_kwork_getonceotherkworkseller',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_kwork_getonceotherkworkseller;
                            }

                            return $ret;
                        }
                        not_api_kwork_getonceotherkworkseller:

                        // api_kwork_getoncesimilarkwork
                        if ('/api/kwork/getoncesimilarkwork' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\GetOnceSimilarKworkController',  '_route' => 'api_kwork_getoncesimilarkwork',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_api_kwork_getoncesimilarkwork;
                            }

                            return $ret;
                        }
                        not_api_kwork_getoncesimilarkwork:

                    }

                }

                // api_kwork_deleteattachment
                if ('/api/kwork/deleteattachment' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\DeleteAttachmentController',  '_route' => 'api_kwork_deleteattachment',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_kwork_deleteattachment;
                    }

                    return $ret;
                }
                not_api_kwork_deleteattachment:

                // api_kwork_checktext
                if ('/api/kwork/checktext' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\CheckTextController',  '_route' => 'api_kwork_checktext',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_kwork_checktext;
                    }

                    return $ret;
                }
                not_api_kwork_checktext:

                // api_kwork_clearactorfilter
                if ('/api/kwork/clearactorfilter' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\ClearActorFilterController',  '_route' => 'api_kwork_clearactorfilter',);
                    if (!in_array($canonicalMethod, ['GET'])) {
                        $allow = array_merge($allow, ['GET']);
                        goto not_api_kwork_clearactorfilter;
                    }

                    return $ret;
                }
                not_api_kwork_clearactorfilter:

                // api_kwork_savefirstphoto
                if ('/api/kwork/savefirstphoto' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Kwork\\SaveFirstPhotoController',  '_route' => 'api_kwork_savefirstphoto',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_kwork_savefirstphoto;
                    }

                    return $ret;
                }
                not_api_kwork_savefirstphoto:

            }

            elseif (0 === strpos($pathinfo, '/api/track')) {
                // api_track_getnewtracks
                if ('/api/track/getnewtracks' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Track\\GetNewTracksController',  '_route' => 'api_track_getnewtracks',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_track_getnewtracks;
                    }

                    return $ret;
                }
                not_api_track_getnewtracks:

                // api_track_getchangedtrack
                if ('/api/track/getchangedtrack' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Track\\GetChangedTrackController',  '_route' => 'api_track_getchangedtrack',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_track_getchangedtrack;
                    }

                    return $ret;
                }
                not_api_track_getchangedtrack:

                // api_track_readtracks
                if ('/api/track/readtracks' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Track\\ReadTracksController',  '_route' => 'api_track_readtracks',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_track_readtracks;
                    }

                    return $ret;
                }
                not_api_track_readtracks:

                // api_track_sendtips
                if ('/api/track/sendtips' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Api\\Track\\SendTipsController',  '_route' => 'api_track_sendtips',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_api_track_sendtips;
                    }

                    return $ret;
                }
                not_api_track_sendtips:

            }

            // api_rating_loadreviews
            if ('/api/rating/loadreviews' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Api\\Rating\\LoadReviewsController',  '_route' => 'api_rating_loadreviews',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_api_rating_loadreviews;
                }

                return $ret;
            }
            not_api_rating_loadreviews:

        }

        // profile_analytics
        if ('/analytics' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\User\\AnalyticsController',  '_route' => 'profile_analytics',);
            if (!in_array($canonicalMethod, ['GET'])) {
                $allow = array_merge($allow, ['GET']);
                goto not_profile_analytics;
            }

            return $ret;
        }
        not_profile_analytics:

        if (0 === strpos($pathinfo, '/o')) {
            if (0 === strpos($pathinfo, '/orders')) {
                // payer_orders
                if ('/orders' === $pathinfo) {
                    return array (  '_controller' => 'Controllers\\Order\\PayerOrdersController',  '_route' => 'payer_orders',);
                }

                // set_user_order_name
                if ('/orders/set_user_order_name' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Order\\ChangeOrderNameController',  '_route' => 'set_user_order_name',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_set_user_order_name;
                    }

                    return $ret;
                }
                not_set_user_order_name:

            }

            // offers
            if ('/offers' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Want\\Worker\\OffersController',  '_route' => 'offers',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_offers;
                }

                return $ret;
            }
            not_offers:

            // offer_highlight
            if (0 === strpos($pathinfo, '/offer_highlight') && preg_match('#^/offer_highlight/(?P<id>\\d+)$#sD', $pathinfo, $matches)) {
                $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'offer_highlight']), array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\HighlightOfferController',));
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_offer_highlight;
                }

                return $ret;
            }
            not_offer_highlight:

        }

        elseif (0 === strpos($pathinfo, '/manage_')) {
            // worker_orders
            if ('/manage_orders' === $pathinfo) {
                return array (  '_controller' => 'Controllers\\Order\\WorkerOrdersController',  '_route' => 'worker_orders',);
            }

            // manage_projects
            if ('/manage_projects' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\WantsController',  '_route' => 'manage_projects',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_manage_projects;
                }

                return $ret;
            }
            not_manage_projects:

            if (0 === strpos($pathinfo, '/manage_kworks')) {
                // manage_kworks
                if ('/manage_kworks' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Kwork\\ManageKworksController',  '_route' => 'manage_kworks',);
                    if (!in_array($canonicalMethod, ['GET'])) {
                        $allow = array_merge($allow, ['GET']);
                        goto not_manage_kworks;
                    }

                    return $ret;
                }
                not_manage_kworks:

                // delete_kworks
                if ('/manage_kworks' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Kwork\\DeleteKworksController',  '_route' => 'delete_kworks',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_delete_kworks;
                    }

                    return $ret;
                }
                not_delete_kworks:

            }

        }

        elseif (0 === strpos($pathinfo, '/track')) {
            // track
            if ('/track' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Track\\TrackController',  '_route' => 'track',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_track;
                }

                return $ret;
            }
            not_track:

            // track_show_hidden
            if ('/track_show_hidden' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Track\\ShowHiddenTracksController',  '_route' => 'track_show_hidden',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_track_show_hidden;
                }

                return $ret;
            }
            not_track_show_hidden:

            // track_order_getupdates
            if ('/track/order/getupdates' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Track\\GetOrderUpdatesController',  '_route' => 'track_order_getupdates',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_track_order_getupdates;
                }

                return $ret;
            }
            not_track_order_getupdates:

            // track_get_available_cancel_reasons
            if ('/track/get_available_cancel_reasons' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Track\\GetAvailableCancelReasonsHtmlController',  '_route' => 'track_get_available_cancel_reasons',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_track_get_available_cancel_reasons;
                }

                return $ret;
            }
            not_track_get_available_cancel_reasons:

            if (0 === strpos($pathinfo, '/track/action')) {
                if (0 === strpos($pathinfo, '/track/action/remove')) {
                    // remove_track
                    if ('/track/action/remove' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\RemoveHandlerController',  '_route' => 'remove_track',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_remove_track;
                        }

                        return $ret;
                    }
                    not_remove_track:

                    // track_remove_extra
                    if ('/track/action/remove_extra' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Extra\\Worker\\RemoveExtraHandlerController',  '_route' => 'track_remove_extra',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_remove_extra;
                        }

                        return $ret;
                    }
                    not_track_remove_extra:

                }

                // track_readpagemsg
                if ('/track/action/readpagemsg' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\ReadPageMsgHandlerController',  '_route' => 'track_readpagemsg',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_track_readpagemsg;
                    }

                    return $ret;
                }
                not_track_readpagemsg:

                if (0 === strpos($pathinfo, '/track/action/payer_inprogress_')) {
                    if (0 === strpos($pathinfo, '/track/action/payer_inprogress_cancel_')) {
                        // track_payer_inprogress_cancel_confirm
                        if ('/track/action/payer_inprogress_cancel_confirm' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Payer\\Inprogress\\CancelConfirmHandlerController',  '_route' => 'track_payer_inprogress_cancel_confirm',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_track_payer_inprogress_cancel_confirm;
                            }

                            return $ret;
                        }
                        not_track_payer_inprogress_cancel_confirm:

                        // track_payer_inprogress_cancel_reject
                        if ('/track/action/payer_inprogress_cancel_reject' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Payer\\Inprogress\\CancelRejectHandlerController',  '_route' => 'track_payer_inprogress_cancel_reject',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_track_payer_inprogress_cancel_reject;
                            }

                            return $ret;
                        }
                        not_track_payer_inprogress_cancel_reject:

                        // track_payer_inprogress_cancel_delete
                        if ('/track/action/payer_inprogress_cancel_delete' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Payer\\Inprogress\\CancelDeleteHandlerController',  '_route' => 'track_payer_inprogress_cancel_delete',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_track_payer_inprogress_cancel_delete;
                            }

                            return $ret;
                        }
                        not_track_payer_inprogress_cancel_delete:

                    }

                    // track_payer_inprogress_done
                    if ('/track/action/payer_inprogress_done' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Payer\\Inprogress\\DoneHandlerController',  '_route' => 'track_payer_inprogress_done',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_payer_inprogress_done;
                        }

                        return $ret;
                    }
                    not_track_payer_inprogress_done:

                    // track_payer_inprogress_arbitrage
                    if ('/track/action/payer_inprogress_arbitrage' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Payer\\Inprogress\\ArbitrageHandlerController',  '_route' => 'track_payer_inprogress_arbitrage',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_payer_inprogress_arbitrage;
                        }

                        return $ret;
                    }
                    not_track_payer_inprogress_arbitrage:

                }

                elseif (0 === strpos($pathinfo, '/track/action/payer_check_')) {
                    // track_payer_check_inprogress
                    if ('/track/action/payer_check_inprogress' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Payer\\Check\\InprogressHandlerController',  '_route' => 'track_payer_check_inprogress',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_payer_check_inprogress;
                        }

                        return $ret;
                    }
                    not_track_payer_check_inprogress:

                    // track_payer_check_done
                    if ('/track/action/payer_check_done' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Payer\\Check\\DoneHandlerController',  '_route' => 'track_payer_check_done',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_payer_check_done;
                        }

                        return $ret;
                    }
                    not_track_payer_check_done:

                    // track_payer_check_arbitrage
                    if ('/track/action/payer_check_arbitrage' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Payer\\Check\\ArbitrageHandlerController',  '_route' => 'track_payer_check_arbitrage',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_payer_check_arbitrage;
                        }

                        return $ret;
                    }
                    not_track_payer_check_arbitrage:

                }

                elseif (0 === strpos($pathinfo, '/track/action/worker_')) {
                    // track_worker_report_new
                    if ('/track/action/worker_report_new' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Worker\\NewReportHandlerController',  '_route' => 'track_worker_report_new',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_worker_report_new;
                        }

                        return $ret;
                    }
                    not_track_worker_report_new:

                    // track_worker_inwork
                    if ('/track/action/worker_inwork' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Worker\\InworkHandlerController',  '_route' => 'track_worker_inwork',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_worker_inwork;
                        }

                        return $ret;
                    }
                    not_track_worker_inwork:

                    if (0 === strpos($pathinfo, '/track/action/worker_inprogress_')) {
                        if (0 === strpos($pathinfo, '/track/action/worker_inprogress_cancel_')) {
                            // track_worker_inprogress_cancel_delete
                            if ('/track/action/worker_inprogress_cancel_delete' === $pathinfo) {
                                $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Worker\\Inprogress\\CancelDeleteHandlerController',  '_route' => 'track_worker_inprogress_cancel_delete',);
                                if (!in_array($requestMethod, ['POST'])) {
                                    $allow = array_merge($allow, ['POST']);
                                    goto not_track_worker_inprogress_cancel_delete;
                                }

                                return $ret;
                            }
                            not_track_worker_inprogress_cancel_delete:

                            // track_worker_inprogress_cancel_reject
                            if ('/track/action/worker_inprogress_cancel_reject' === $pathinfo) {
                                $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Worker\\Inprogress\\CancelRejectHandlerController',  '_route' => 'track_worker_inprogress_cancel_reject',);
                                if (!in_array($requestMethod, ['POST'])) {
                                    $allow = array_merge($allow, ['POST']);
                                    goto not_track_worker_inprogress_cancel_reject;
                                }

                                return $ret;
                            }
                            not_track_worker_inprogress_cancel_reject:

                            // track_worker_inprogress_cancel_confirm
                            if ('/track/action/worker_inprogress_cancel_confirm' === $pathinfo) {
                                $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Worker\\Inprogress\\CancelConfirmHandlerController',  '_route' => 'track_worker_inprogress_cancel_confirm',);
                                if (!in_array($requestMethod, ['POST'])) {
                                    $allow = array_merge($allow, ['POST']);
                                    goto not_track_worker_inprogress_cancel_confirm;
                                }

                                return $ret;
                            }
                            not_track_worker_inprogress_cancel_confirm:

                        }

                        // track_worker_inprogress_check
                        if ('/track/action/worker_inprogress_check' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Worker\\Inprogress\\CheckHandlerController',  '_route' => 'track_worker_inprogress_check',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_track_worker_inprogress_check;
                            }

                            return $ret;
                        }
                        not_track_worker_inprogress_check:

                        // track_worker_inprogress_arbitrage
                        if ('/track/action/worker_inprogress_arbitrage' === $pathinfo) {
                            $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Worker\\Inprogress\\ArbitrageHandlerController',  '_route' => 'track_worker_inprogress_arbitrage',);
                            if (!in_array($requestMethod, ['POST'])) {
                                $allow = array_merge($allow, ['POST']);
                                goto not_track_worker_inprogress_arbitrage;
                            }

                            return $ret;
                        }
                        not_track_worker_inprogress_arbitrage:

                    }

                    // track_worker_check_arbitrage
                    if ('/track/action/worker_check_arbitrage' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Worker\\Check\\ArbitrageHandlerController',  '_route' => 'track_worker_check_arbitrage',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_worker_check_arbitrage;
                        }

                        return $ret;
                    }
                    not_track_worker_check_arbitrage:

                }

                // track_cancel_order
                if ('/track/action/cancel_order' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\CancelOrderHandlerController',  '_route' => 'track_cancel_order',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_track_cancel_order;
                    }

                    return $ret;
                }
                not_track_cancel_order:

                if (0 === strpos($pathinfo, '/track/action/a')) {
                    // track_extra_approveextrassubmited
                    if ('/track/action/approveextrassubmited' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Extra\\Payer\\ApproveExtrasHandlerController',  '_route' => 'track_extra_approveextrassubmited',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_extra_approveextrassubmited;
                        }

                        return $ret;
                    }
                    not_track_extra_approveextrassubmited:

                    // track_extra_addextrassubmited
                    if ('/track/action/addextrassubmited' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Extra\\Payer\\AddExtrasHandlerController',  '_route' => 'track_extra_addextrassubmited',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_extra_addextrassubmited;
                        }

                        return $ret;
                    }
                    not_track_extra_addextrassubmited:

                    // track_extra_addpackagessubmited
                    if ('/track/action/addpackagessubmited' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Extra\\Payer\\AddExtrasHandlerController',  '_route' => 'track_extra_addpackagessubmited',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_track_extra_addpackagessubmited;
                        }

                        return $ret;
                    }
                    not_track_extra_addpackagessubmited:

                }

                // track_extra_declineextrassubmited
                if ('/track/action/declineextrassubmited' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Extra\\Payer\\DeclineExtrasHandlerController',  '_route' => 'track_extra_declineextrassubmited',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_track_extra_declineextrassubmited;
                    }

                    return $ret;
                }
                not_track_extra_declineextrassubmited:

                // track_extra_declineextrassuggestion
                if ('/track/action/declineextrassuggestion' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Extra\\Worker\\DeclineExtrasHandlerController',  '_route' => 'track_extra_declineextrassuggestion',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_track_extra_declineextrassuggestion;
                    }

                    return $ret;
                }
                not_track_extra_declineextrassuggestion:

                // track_extra_upgradepackagesubmited
                if ('/track/action/upgradepackagesubmited' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\Extra\\Payer\\UpgradePackageHandlerController',  '_route' => 'track_extra_upgradepackagesubmited',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_track_extra_upgradepackagesubmited;
                    }

                    return $ret;
                }
                not_track_extra_upgradepackagesubmited:

                // track_text
                if ('/track/action/text' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\MessageHandlerController',  '_route' => 'track_text',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_track_text;
                    }

                    return $ret;
                }
                not_track_text:

                // track_edit
                if ('/track/action/edit' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\EditHandlerController',  '_route' => 'track_edit',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_track_edit;
                    }

                    return $ret;
                }
                not_track_edit:

                // track_instruction
                if ('/track/action/instruction' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Track\\Handler\\InstructionHandlerController',  '_route' => 'track_instruction',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_track_instruction;
                    }

                    return $ret;
                }
                not_track_instruction:

            }

        }

        elseif (0 === strpos($pathinfo, '/project')) {
            if (0 === strpos($pathinfo, '/projects')) {
                // projects_worker
                if ('/projects' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Worker\\WantsController',  '_route' => 'projects_worker',);
                    if (!in_array($canonicalMethod, ['GET'])) {
                        $allow = array_merge($allow, ['GET']);
                        goto not_projects_worker;
                    }

                    return $ret;
                }
                not_projects_worker:

                // projects_ajax_loading
                if ('/projects' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Worker\\AjaxLoadingController',  '_route' => 'projects_ajax_loading',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_projects_ajax_loading;
                    }

                    return $ret;
                }
                not_projects_ajax_loading:

                // projects_ajax_parameters_loading
                if ('/projects_params' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Worker\\AjaxWantsListParametersController',  '_route' => 'projects_ajax_parameters_loading',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_projects_ajax_parameters_loading;
                    }

                    return $ret;
                }
                not_projects_ajax_parameters_loading:

                if (0 === strpos($pathinfo, '/projects/manage')) {
                    // manage_projects_stop_handler
                    if ('/projects/manage/stop' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\StopWantHandlerController',  '_route' => 'manage_projects_stop_handler',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_manage_projects_stop_handler;
                        }

                        return $ret;
                    }
                    not_manage_projects_stop_handler:

                    // manage_projects_restart_handler
                    if ('/projects/manage/restart' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\RestartWantHandlerController',  '_route' => 'manage_projects_restart_handler',);
                        if (!in_array($canonicalMethod, ['GET'])) {
                            $allow = array_merge($allow, ['GET']);
                            goto not_manage_projects_restart_handler;
                        }

                        return $ret;
                    }
                    not_manage_projects_restart_handler:

                    // manage_projects_delete_handler
                    if ('/projects/manage/delete' === $pathinfo) {
                        $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\DeleteWantHandlerController',  '_route' => 'manage_projects_delete_handler',);
                        if (!in_array($requestMethod, ['POST'])) {
                            $allow = array_merge($allow, ['POST']);
                            goto not_manage_projects_delete_handler;
                        }

                        return $ret;
                    }
                    not_manage_projects_delete_handler:

                }

            }

            // view_offers
            if ('/project' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\OffersController',  '_route' => 'view_offers',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_view_offers;
                }

                return $ret;
            }
            not_view_offers:

            if (0 === strpos($pathinfo, '/projects')) {
                // view_offers_all
                if (preg_match('#^/projects/(?P<id>\\d+)$#sD', $pathinfo, $matches)) {
                    $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'view_offers_all']), array (  '_controller' => 'Controllers\\Want\\Payer\\OffersController',));
                    if (!in_array($canonicalMethod, ['GET'])) {
                        $allow = array_merge($allow, ['GET']);
                        goto not_view_offers_all;
                    }

                    return $ret;
                }
                not_view_offers_all:

                // check_is_template
                if ('/projects/check_is_template' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Worker\\CheckCloneOfferController',  '_route' => 'check_is_template',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_check_is_template;
                    }

                    return $ret;
                }
                not_check_is_template:

                // wants_unsubscribe
                if ('/projects/unsubscribe' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\UserNotificationPeriod\\UnsubscribeNotificationController',  '_route' => 'wants_unsubscribe',);
                    if (!in_array($canonicalMethod, ['GET'])) {
                        $allow = array_merge($allow, ['GET']);
                        goto not_wants_unsubscribe;
                    }

                    return $ret;
                }
                not_wants_unsubscribe:

                // wants_user_list
                if (0 === strpos($pathinfo, '/projects/list') && preg_match('#^/projects/list/(?P<username>[^/]++)$#sD', $pathinfo, $matches)) {
                    $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'wants_user_list']), array (  '_controller' => 'Controllers\\Want\\Worker\\PayerWantsController',));
                    if (!in_array($canonicalMethod, ['GET'])) {
                        $allow = array_merge($allow, ['GET']);
                        goto not_wants_user_list;
                    }

                    return $ret;
                }
                not_wants_user_list:

            }

            // order_by_offer
            if ('/project/order' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\OrderHandlerController',  '_route' => 'order_by_offer',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_order_by_offer;
                }

                return $ret;
            }
            not_order_by_offer:

        }

        elseif (0 === strpos($pathinfo, '/edit')) {
            // edit_offer
            if ('/edit_offer' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Want\\Worker\\EditOfferController',  '_route' => 'edit_offer',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_edit_offer;
                }

                return $ret;
            }
            not_edit_offer:

            if (0 === strpos($pathinfo, '/edit_project')) {
                // edit_project
                if ('/edit_project' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\EditWantController',  '_route' => 'edit_project',);
                    if (!in_array($canonicalMethod, ['GET'])) {
                        $allow = array_merge($allow, ['GET']);
                        goto not_edit_project;
                    }

                    return $ret;
                }
                not_edit_project:

                // edit_project_form_handler
                if ('/edit_project' === $pathinfo) {
                    $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\EditWantHandlerController',  '_route' => 'edit_project_form_handler',);
                    if (!in_array($requestMethod, ['POST'])) {
                        $allow = array_merge($allow, ['POST']);
                        goto not_edit_project_form_handler;
                    }

                    return $ret;
                }
                not_edit_project_form_handler:

            }

            // kwork_edit
            if ('/edit' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Kwork\\AddEditKworkController',  '_route' => 'kwork_edit',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_kwork_edit;
                }

                return $ret;
            }
            not_kwork_edit:

            // kwork_save_edit
            if ('/edit' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Kwork\\SaveKworkController',  '_route' => 'kwork_save_edit',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_kwork_save_edit;
                }

                return $ret;
            }
            not_kwork_save_edit:

        }

        // remove_offer_highlight
        if (0 === strpos($pathinfo, '/remove_offer_highlight') && preg_match('#^/remove_offer_highlight/(?P<id>\\d+)$#sD', $pathinfo, $matches)) {
            $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'remove_offer_highlight']), array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\RemoveHighlightOfferController',));
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_remove_offer_highlight;
            }

            return $ret;
        }
        not_remove_offer_highlight:

        // remove_hide_offer
        if (0 === strpos($pathinfo, '/remove_hide_offer') && preg_match('#^/remove_hide_offer/(?P<id>\\d+)$#sD', $pathinfo, $matches)) {
            $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'remove_hide_offer']), array (  '_controller' => 'Controllers\\Want\\Payer\\Handler\\RemoveHideOfferController',));
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_remove_hide_offer;
            }

            return $ret;
        }
        not_remove_hide_offer:

        // wants_portfolio
        if ('/wants/portfolio' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\Want\\Payer\\PortfolioController',  '_route' => 'wants_portfolio',);
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_wants_portfolio;
            }

            return $ret;
        }
        not_wants_portfolio:

        // logout
        if ('/logout' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\Security\\LogoutController',  '_route' => 'logout',);
            if (!in_array($canonicalMethod, ['GET'])) {
                $allow = array_merge($allow, ['GET']);
                goto not_logout;
            }

            return $ret;
        }
        not_logout:

        // change_usertype
        if ('/change_usertype' === $pathinfo) {
            return array (  '_controller' => 'Controllers\\User\\ChangeUserTypeController',  '_route' => 'change_usertype',);
        }

        // check_payer_phone_verified
        if ('/check_payer_phone_verification' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\User\\CheckPayerPhoneVerification',  '_route' => 'check_payer_phone_verified',);
            if (!in_array($canonicalMethod, ['GET'])) {
                $allow = array_merge($allow, ['GET']);
                goto not_check_payer_phone_verified;
            }

            return $ret;
        }
        not_check_payer_phone_verified:

        // set_kwork_name
        if ('/set_kwork_name' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\Kwork\\SetKworkNameController',  '_route' => 'set_kwork_name',);
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_set_kwork_name;
            }

            return $ret;
        }
        not_set_kwork_name:

        if (0 === strpos($pathinfo, '/kwork_ratings_update')) {
            // kwork_ratings_update
            if ('/kwork_ratings_update' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Kwork\\RatingsController',  '_route' => 'kwork_ratings_update',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_kwork_ratings_update;
                }

                return $ret;
            }
            not_kwork_ratings_update:

            // kwork_ratings_update_post
            if ('/kwork_ratings_update' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Kwork\\UpdateRatingsController',  '_route' => 'kwork_ratings_update_post',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_kwork_ratings_update_post;
                }

                return $ret;
            }
            not_kwork_ratings_update_post:

        }

        elseif (0 === strpos($pathinfo, '/balance')) {
            // balance_add
            if ('/balance' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Balance\\AddController',  '_route' => 'balance_add',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_balance_add;
                }

                return $ret;
            }
            not_balance_add:

            // balance
            if ('/balance' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\Balance\\IndexController',  '_route' => 'balance',);
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_balance;
                }

                return $ret;
            }
            not_balance:

        }

        elseif (0 === strpos($pathinfo, '/user')) {
            // get_profile_portfolios
            if ('/user/portfolio' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\User\\PortfolioController',  '_route' => 'get_profile_portfolios',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_get_profile_portfolios;
                }

                return $ret;
            }
            not_get_profile_portfolios:

            // profile_view
            if (preg_match('#^/user/(?P<username>[^/]++)$#sD', $pathinfo, $matches)) {
                $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'profile_view']), array (  '_controller' => 'Controllers\\User\\ProfileController',));
                if (!in_array($canonicalMethod, ['GET'])) {
                    $allow = array_merge($allow, ['GET']);
                    goto not_profile_view;
                }

                return $ret;
            }
            not_profile_view:

            // change_profile_cover
            if (preg_match('#^/user/(?P<username>[^/]++)/update_cover$#sD', $pathinfo, $matches)) {
                $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'change_profile_cover']), array (  '_controller' => 'Controllers\\User\\UpdateCoverController',));
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_change_profile_cover;
                }

                return $ret;
            }
            not_change_profile_cover:

            // set_user_notification_period
            if ('/user_notification_period/set' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\UserNotificationPeriod\\SetNotificationPeriodController',  '_route' => 'set_user_notification_period',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_set_user_notification_period;
                }

                return $ret;
            }
            not_set_user_notification_period:

            // get_user_notification_period
            if ('/user_notification_period/get' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\UserNotificationPeriod\\GetNotificationPeriodController',  '_route' => 'get_user_notification_period',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_get_user_notification_period;
                }

                return $ret;
            }
            not_get_user_notification_period:

            // update_user_avatar
            if ('/user/update_avatar' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\User\\UpdateAvatarController',  '_route' => 'update_user_avatar',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_update_user_avatar;
                }

                return $ret;
            }
            not_update_user_avatar:

            // change_kwork_book_info_block_state
            if ('/user/kwork_book_info_block' === $pathinfo) {
                $ret = array (  '_controller' => 'Controllers\\User\\KworkBookInfoBlockController',  '_route' => 'change_kwork_book_info_block_state',);
                if (!in_array($requestMethod, ['POST'])) {
                    $allow = array_merge($allow, ['POST']);
                    goto not_change_kwork_book_info_block_state;
                }

                return $ret;
            }
            not_change_kwork_book_info_block_state:

        }

        // review_create
        if (preg_match('#^/(?P<orderId>\\d+)/addReview$#sD', $pathinfo, $matches)) {
            $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'review_create']), array (  '_controller' => 'Controllers\\Track\\Review\\CreateReviewController',));
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_review_create;
            }

            return $ret;
        }
        not_review_create:

        // review_update
        if (preg_match('#^/(?P<orderId>\\d+)/editReview$#sD', $pathinfo, $matches)) {
            $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'review_update']), array (  '_controller' => 'Controllers\\Track\\Review\\UpdateReviewController',));
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_review_update;
            }

            return $ret;
        }
        not_review_update:

        // review_remove
        if (preg_match('#^/(?P<orderId>\\d+)/review/remove$#sD', $pathinfo, $matches)) {
            $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'review_remove']), array (  '_controller' => 'Controllers\\Track\\Review\\RemoveReviewController',));
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_review_remove;
            }

            return $ret;
        }
        not_review_remove:

        // review_create_comment
        if (preg_match('#^/(?P<orderId>\\d+)/review/comment$#sD', $pathinfo, $matches)) {
            $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'review_create_comment']), array (  '_controller' => 'Controllers\\Track\\Review\\CreateReviewCommentController',));
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_review_create_comment;
            }

            return $ret;
        }
        not_review_create_comment:

        // review_update_comment
        if (preg_match('#^/(?P<orderId>\\d+)/review/comment/update$#sD', $pathinfo, $matches)) {
            $ret = $this->mergeDefaults(array_replace($matches, ['_route' => 'review_update_comment']), array (  '_controller' => 'Controllers\\Track\\Review\\UpdateReviewCommentController',));
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_review_update_comment;
            }

            return $ret;
        }
        not_review_update_comment:

        // check_review_text
        if ('/review/check_text' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\Track\\Review\\CheckReviewTextController',  '_route' => 'check_review_text',);
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_check_review_text;
            }

            return $ret;
        }
        not_check_review_text:

        // send_review_comment
        if ('/send_review_comment' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\Review\\CreateCommentController',  '_route' => 'send_review_comment',);
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_send_review_comment;
            }

            return $ret;
        }
        not_send_review_comment:

        // edit_review_comment
        if ('/edit_review_comment' === $pathinfo) {
            $ret = array (  '_controller' => 'Controllers\\Review\\EditCommentController',  '_route' => 'edit_review_comment',);
            if (!in_array($requestMethod, ['POST'])) {
                $allow = array_merge($allow, ['POST']);
                goto not_edit_review_comment;
            }

            return $ret;
        }
        not_edit_review_comment:

        // catalog_kworks
        if (0 === strpos($pathinfo, '/categories') && preg_match('#^/categories/(?P<alias>[^/]++)$#sD', $pathinfo, $matches)) {
            return $this->mergeDefaults(array_replace($matches, ['_route' => 'catalog_kworks']), array (  '_controller' => 'Controllers\\Catalog\\KworksViewController',));
        }

        // kwork_view
        if (preg_match('#^/(?P<seo>((?!track).)*)/(?P<kworkId>\\d+)/(?P<kworkSeoTitle>[^/]++)$#sD', $pathinfo, $matches)) {
            return $this->mergeDefaults(array_replace($matches, ['_route' => 'kwork_view']), array (  '_controller' => 'Controllers\\Kwork\\ViewKworkController',));
        }

        if ('/' === $pathinfo && !$allow) {
            throw new Symfony\Component\Routing\Exception\NoConfigurationException();
        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
