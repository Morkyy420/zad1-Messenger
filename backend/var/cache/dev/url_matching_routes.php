<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/_wdt/styles' => [[['_route' => '_wdt_stylesheet', '_controller' => 'web_profiler.controller.profiler::toolbarStylesheetAction'], null, null, null, false, false, null]],
        '/_profiler' => [[['_route' => '_profiler_home', '_controller' => 'web_profiler.controller.profiler::homeAction'], null, null, null, true, false, null]],
        '/_profiler/search' => [[['_route' => '_profiler_search', '_controller' => 'web_profiler.controller.profiler::searchAction'], null, null, null, false, false, null]],
        '/_profiler/search_bar' => [[['_route' => '_profiler_search_bar', '_controller' => 'web_profiler.controller.profiler::searchBarAction'], null, null, null, false, false, null]],
        '/_profiler/phpinfo' => [[['_route' => '_profiler_phpinfo', '_controller' => 'web_profiler.controller.profiler::phpinfoAction'], null, null, null, false, false, null]],
        '/_profiler/xdebug' => [[['_route' => '_profiler_xdebug', '_controller' => 'web_profiler.controller.profiler::xdebugAction'], null, null, null, false, false, null]],
        '/_profiler/open' => [[['_route' => '_profiler_open_file', '_controller' => 'web_profiler.controller.profiler::openAction'], null, null, null, false, false, null]],
        '/api/auth/register' => [[['_route' => 'register', '_controller' => 'App\\Controller\\AuthController::register'], null, ['POST' => 0], null, false, false, null]],
        '/api/auth/login' => [[['_route' => 'login', '_controller' => 'App\\Controller\\AuthController::login'], null, ['POST' => 0], null, false, false, null]],
        '/api/auth/ip' => [[['_route' => 'get_ip', '_controller' => 'App\\Controller\\AuthController::getClientIp'], null, ['GET' => 0], null, false, false, null]],
        '/api/auth/users' => [[['_route' => 'get_users', '_controller' => 'App\\Controller\\AuthController::getUsers'], null, ['GET' => 0], null, false, false, null]],
        '/api/messages' => [
            [['_route' => 'post_message', '_controller' => 'App\\Controller\\ChatController::postMessage'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'get_messages', '_controller' => 'App\\Controller\\ChatController::getMessages'], null, ['GET' => 0], null, false, false, null],
        ],
        '/api/events' => [
            [['_route' => 'get_all_events', '_controller' => 'App\\Controller\\EventController::getAllEvents'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'create_event', '_controller' => 'App\\Controller\\EventController::createEvent'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/photos' => [[['_route' => 'upload_photo', '_controller' => 'App\\Controller\\PhotoController::uploadPhoto'], null, ['POST' => 0], null, false, false, null]],
        '/api/todos' => [[['_route' => 'create_todo', '_controller' => 'App\\Controller\\TodoController::createTodo'], null, ['POST' => 0], null, false, false, null]],
        '/' => [[['_route' => 'app_home', '_controller' => 'App\\Controller\\FrontendController::index'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_(?'
                    .'|error/(\\d+)(?:\\.([^/]++))?(*:38)'
                    .'|wdt/([^/]++)(*:57)'
                    .'|profiler/(?'
                        .'|font/([^/\\.]++)\\.woff2(*:98)'
                        .'|([^/]++)(?'
                            .'|/(?'
                                .'|search/results(*:134)'
                                .'|router(*:148)'
                                .'|exception(?'
                                    .'|(*:168)'
                                    .'|\\.css(*:181)'
                                .')'
                            .')'
                            .'|(*:191)'
                        .')'
                    .')'
                .')'
                .'|/api/(?'
                    .'|auth/users/([^/]++)/friends(?'
                        .'|(*:240)'
                    .')'
                    .'|messages/([^/]++)/reaction(*:275)'
                    .'|conversations/(?'
                        .'|([^/]++)(?'
                            .'|(*:311)'
                            .'|/(?'
                                .'|with/([^/]++)(*:336)'
                                .'|messages(?'
                                    .'|(*:355)'
                                    .'|/upload(*:370)'
                                .')'
                            .')'
                        .')'
                        .'|messages/([^/]++)/read(*:403)'
                        .'|([^/]++)/messages/(?'
                            .'|mark\\-read(*:442)'
                            .'|([^/]++)/reaction(*:467)'
                        .')'
                        .'|messages/([^/]++)/(?'
                            .'|edit(*:501)'
                            .'|delete(*:515)'
                        .')'
                        .'|([^/]++)/typing(?'
                            .'|(*:542)'
                            .'|\\-status(*:558)'
                            .'|/clear(*:572)'
                        .')'
                    .')'
                    .'|events/([^/]++)(*:597)'
                    .'|photos/([^/]++)(?'
                        .'|(*:623)'
                        .'|(*:631)'
                    .')'
                    .'|todos/([^/]++)(?'
                        .'|(*:657)'
                        .'|(*:665)'
                    .')'
                .')'
                .'|/((?!api|uploads).*)(*:695)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        38 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        57 => [[['_route' => '_wdt', '_controller' => 'web_profiler.controller.profiler::toolbarAction'], ['token'], null, null, false, true, null]],
        98 => [[['_route' => '_profiler_font', '_controller' => 'web_profiler.controller.profiler::fontAction'], ['fontName'], null, null, false, false, null]],
        134 => [[['_route' => '_profiler_search_results', '_controller' => 'web_profiler.controller.profiler::searchResultsAction'], ['token'], null, null, false, false, null]],
        148 => [[['_route' => '_profiler_router', '_controller' => 'web_profiler.controller.router::panelAction'], ['token'], null, null, false, false, null]],
        168 => [[['_route' => '_profiler_exception', '_controller' => 'web_profiler.controller.exception_panel::body'], ['token'], null, null, false, false, null]],
        181 => [[['_route' => '_profiler_exception_css', '_controller' => 'web_profiler.controller.exception_panel::stylesheet'], ['token'], null, null, false, false, null]],
        191 => [[['_route' => '_profiler', '_controller' => 'web_profiler.controller.profiler::panelAction'], ['token'], null, null, false, true, null]],
        240 => [
            [['_route' => 'add_friend', '_controller' => 'App\\Controller\\AuthController::addFriend'], ['id'], ['POST' => 0], null, false, false, null],
            [['_route' => 'get_friends', '_controller' => 'App\\Controller\\AuthController::getFriends'], ['id'], ['GET' => 0], null, false, false, null],
        ],
        275 => [[['_route' => 'add_reaction', '_controller' => 'App\\Controller\\ChatController::addReaction'], ['id'], ['POST' => 0], null, false, false, null]],
        311 => [[['_route' => 'get_user_conversations', '_controller' => 'App\\Controller\\ConversationController::getUserConversations'], ['userId'], ['GET' => 0], null, false, true, null]],
        336 => [[['_route' => 'get_or_create_conversation', '_controller' => 'App\\Controller\\ConversationController::getOrCreateConversation'], ['userId', 'friendId'], ['GET' => 0], null, false, true, null]],
        355 => [
            [['_route' => 'get_conversation_messages', '_controller' => 'App\\Controller\\ConversationController::getMessages'], ['conversationId'], ['GET' => 0], null, false, false, null],
            [['_route' => 'post_message_to_conversation', '_controller' => 'App\\Controller\\ConversationController::postMessage'], ['conversationId'], ['POST' => 0], null, false, false, null],
        ],
        370 => [[['_route' => 'upload_message_attachment', '_controller' => 'App\\Controller\\ConversationController::uploadAttachment'], ['conversationId'], ['POST' => 0, 'OPTIONS' => 1], null, false, false, null]],
        403 => [[['_route' => 'mark_message_read', '_controller' => 'App\\Controller\\ConversationController::markMessageAsRead'], ['messageId'], ['POST' => 0], null, false, false, null]],
        442 => [[['_route' => 'mark_conversation_read', '_controller' => 'App\\Controller\\ConversationController::markConversationAsRead'], ['conversationId'], ['POST' => 0], null, false, false, null]],
        467 => [[['_route' => 'add_message_reaction', '_controller' => 'App\\Controller\\ConversationController::addReaction'], ['conversationId', 'messageId'], ['POST' => 0], null, false, false, null]],
        501 => [[['_route' => 'edit_message', '_controller' => 'App\\Controller\\ConversationController::editMessage'], ['messageId'], ['PUT' => 0], null, false, false, null]],
        515 => [[['_route' => 'delete_message', '_controller' => 'App\\Controller\\ConversationController::deleteMessage'], ['messageId'], ['DELETE' => 0], null, false, false, null]],
        542 => [[['_route' => 'set_typing', '_controller' => 'App\\Controller\\ConversationController::setTyping'], ['conversationId'], ['POST' => 0], null, false, false, null]],
        558 => [[['_route' => 'get_typing_status', '_controller' => 'App\\Controller\\ConversationController::getTypingStatus'], ['conversationId'], ['GET' => 0], null, false, false, null]],
        572 => [[['_route' => 'clear_typing', '_controller' => 'App\\Controller\\ConversationController::clearTyping'], ['conversationId'], ['POST' => 0], null, false, false, null]],
        597 => [[['_route' => 'delete_event', '_controller' => 'App\\Controller\\EventController::deleteEvent'], ['id'], ['DELETE' => 0], null, false, true, null]],
        623 => [[['_route' => 'get_user_photos', '_controller' => 'App\\Controller\\PhotoController::getUserPhotos'], ['userId'], ['GET' => 0], null, false, true, null]],
        631 => [
            [['_route' => 'update_photo_caption', '_controller' => 'App\\Controller\\PhotoController::updatePhotoCaption'], ['id'], ['PATCH' => 0], null, false, true, null],
            [['_route' => 'delete_photo', '_controller' => 'App\\Controller\\PhotoController::deletePhoto'], ['id'], ['DELETE' => 0], null, false, true, null],
        ],
        657 => [[['_route' => 'get_user_todos', '_controller' => 'App\\Controller\\TodoController::getUserTodos'], ['userId'], ['GET' => 0], null, false, true, null]],
        665 => [
            [['_route' => 'update_todo', '_controller' => 'App\\Controller\\TodoController::updateTodo'], ['id'], ['PATCH' => 0], null, false, true, null],
            [['_route' => 'delete_todo', '_controller' => 'App\\Controller\\TodoController::deleteTodo'], ['id'], ['DELETE' => 0], null, false, true, null],
        ],
        695 => [
            [['_route' => 'app_frontend', '_controller' => 'App\\Controller\\FrontendController::index'], ['route'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
