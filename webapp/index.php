<?php
/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle
 * management system
 * 
 * WebView PHP/HTML template
 * 
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1.0-beta
 * 
 */
session_start();
$_SESSION['webapp'] = true;
if (!isset($_SESSION['access_token'])){
    header('Location: ../main.php?collection=user&action=login');
}
$subtitle = array(
    'list' => array(
        'all' => addslashes(_('Listing all raffles')),
        'me' =>  addslashes(_("Listing raffles I've joined")),
        'mine' => addslashes(_("Listing my raffles")),
        'open' =>  addslashes(_("Listing open raffles")),
        'result' =>  addslashes(_("Listing last operation's result")),
        'raffle' =>  addslashes(_("Listing raffle")),
        'raffles' =>  addslashes(_("Listing raffles")),
        'newRaffle' =>  addslashes(_("Listing new raffle")),
        'participants' =>  addslashes(_("Listing raffle's participants")),
        'winners' =>  addslashes(_("Listing raffle's winners")),
    )
);
?><!doctype html>
<html>
    <head>
        <!--  
            Google plus raffle app 
            Copyright (c) Gael Abadin (elcodedocle) 2014
            License: MIT Expat
            Version: v0.1.0-beta
        -->
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
        <meta charset="UTF-8" />
        <title>
            <?=htmlentities(_("Google Plus Raffle App by elcodedocle"))?>
        </title>
        <link 
            href="style/style.css" 
            rel="stylesheet" 
            type="text/css" 
        />
        <script 
            type="text/javascript" 
            src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"
        ></script>
        <script 
            type="text/javascript" 
            src="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"
        ></script>
        <script 
            type="text/javascript" 
            src="../components/datatables-tabletools/js/dataTables.tableTools.min.js"
        ></script>
        <script 
            type="text/javascript" 
            src="webappClientController.js"
        ></script>
    </head>
    <body>
        <div class="container" id="container">
            <div id="preAjaxContent" style="display:none;">
                <p>
                    <?= 
                    htmlentities(_("Loading gplusraffle. Please wait")); 
                    ?><span id="dots"></span>
                </p>
            </div>
            <div id="postAjaxContent">
                <div class="Flexible-container">
                    <div class="page-header" id="header">
                        <h1>
                            <?=htmlentities(_("Google Plus Raffle Web Application"));?>
                            <br />
                            <small id="subtitle">
                            </small>
                        </h1>
                    </div>
                    <form 
                        name="inputData" 
                        id="inputData" 
                        onsubmit="
                            if (
                                document.getElementById('newRaffleDescription').value.trim().length > 0
                                || 
                                window.confirm(
                                    '<?=
                                        addslashes(_
                                            (
                                                "Proceed WITHOUT including a DESCRIPTION for your new raffle?"
                                            )
                                        );
                                    ?>'
                                )
                            ){
                                requestAndProcessPageJSONData(
                                    {
                                        'collection':'raffle',
                                        'action':'create',
                                        'description': document.getElementById('newRaffleDescription').value.trim(),
                                        'subtitle': '<?=$subtitle['list']['newRaffle']?>'
                                    }
                                );
                            }
                            return false;
                        "
                    >
                        <div class="box">
                            <div class="request">
                                <button
                                    type='button'
                                    class='button'
                                    value='/user/logout'
                                    onclick="document.location.href='../main.php?collection=user&action=logout'"
                                    >
                                    <?=htmlentities(_("Logout"))?>
                                </button>
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/list/open'
                                    onclick="
                                        requestAndProcessPageJSONData(
                                            {
                                                'collection':'raffle',
                                                'action':'list',
                                                'status':'open',
                                                'subtitle': '<?=$subtitle['list']['open']?>'
                                            }
                                        )
                                    "
                                    >
                                    <?=htmlentities(_("Open raffles"))?>
                                </button>
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/list/me'
                                    onclick="
                                        requestAndProcessPageJSONData(
                                            {
                                                'collection':'raffle',
                                                'action':'list',
                                                'userid':'me',
                                                'subtitle': '<?=$subtitle['list']['me']?>'
                                            }
                                        )
                                    "
                                    >
                                    <?=htmlentities(_("Raffles I've joined"))?>
                                </button>
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/list/mine'
                                    onclick="
                                        requestAndProcessPageJSONData(
                                            {
                                                'collection':'raffle',
                                                'action':'list',
                                                'creatorid':'me',
                                                'subtitle': '<?=$subtitle['list']['mine']?>'
                                            }
                                        )
                                    "
                                    >
                                    <?=htmlentities(_("My raffles"))?>
                                </button>
                                <br />
                                <input name="description" size="36" id="newRaffleDescription" type="text" placeholder="New raffle description" />
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/create'
                                    onclick="if (
                                        document.getElementById('newRaffleDescription').value.trim().length > 0
                                        ||
                                        window.confirm(
                                            '<?=
                                                addslashes(_
                                                    (
                                                        "Proceed WITHOUT including a DESCRIPTION for your new raffle?"
                                                    )
                                                );
                                            ?>'
                                        )
                                    ){
                                        requestAndProcessPageJSONData(
                                            {
                                                'collection':'raffle',
                                                'action':'create',
                                                'description': document.getElementById('newRaffleDescription').value.trim(),
                                                'subtitle': '<?=$subtitle['list']['newRaffle']?>'
                                            }
                                        )
                                    }
                                        "
                                    >
                                    <?=htmlentities(_("Create raffle"))?>
                                </button>
                                <br />
                                <input name="raffleid" size="36" id="raffleId" type="text" placeholder="Raffle Id" />
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/open'
                                    onclick="
                                        if (!/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/.test(
                                            document.getElementById('raffleId').value.trim())
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid raffle identificator."))?>');
                                        } else {
                                            requestAndProcessPageJSONData(
                                                {
                                                    'collection':'raffle',
                                                    'action':'open',
                                                    'raffleid': document.getElementById('raffleId').value.trim(),
                                                    'subtitle': '<?=$subtitle['list']['result']?>'
                                                }
                                            );
                                        }
                                    "
                                    >
                                    <?=htmlentities(_("Open"))?>
                                </button>
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/close'
                                    onclick="
                                        if (!/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/.test(
                                            document.getElementById('raffleId').value.trim())
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid raffle identificator."))?>');
                                        } else {
                                            requestAndProcessPageJSONData(
                                                {
                                                    'collection':'raffle',
                                                    'action':'close',
                                                    'raffleid': document.getElementById('raffleId').value.trim(),
                                                    'subtitle': '<?=$subtitle['list']['result']?>'
                                                }
                                            );
                                        }
                                    "
                                    >
                                    <?=htmlentities(_("Close"))?>
                                </button>
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/delete'
                                    onclick="
                                        if (!/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/.test(
                                        document.getElementById('raffleId').value.trim())
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid raffle identificator."))?>');
                                        } else {
                                            if (window.confirm('<?=_('DELETE the raffle?')?>')){
                                                requestAndProcessPageJSONData(
                                                    {
                                                        'collection':'raffle',
                                                        'action':'delete',
                                                        'raffleid': document.getElementById('raffleId').value.trim(),
                                                        'subtitle': '<?=$subtitle['list']['result']?>'
                                                    }
                                                );
                                            }
                                        }
                                    "
                                    >
                                    <?=htmlentities(_("Delete"))?>
                                </button>
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/leave'
                                    onclick="
                                        if (!/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/.test(
                                        document.getElementById('raffleId').value.trim())
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid raffle identificator."))?>');
                                        } else {
                                            if (window.confirm('<?=_('LEAVE the raffle?')?>')){
                                                requestAndProcessPageJSONData(
                                                    {
                                                        'collection':'raffle',
                                                        'action':'leave',
                                                        'raffleid': document.getElementById('raffleId').value.trim(),
                                                        'subtitle': '<?=$subtitle['list']['result']?>'
                                                    }
                                                );
                                            }
                                        }
                                    "
                                    >
                                    <?=htmlentities(_("Leave"))?>
                                </button>
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/list'
                                    onclick="
                                        if (!/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/.test(
                                            document.getElementById('raffleId').value.trim())
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid raffle identificator."))?>');
                                        } else {
                                            requestAndProcessPageJSONData(
                                            {
                                                'collection':'raffle',
                                                'action':'list',
                                                'raffleid': document.getElementById('raffleId').value.trim(),
                                                'subtitle': '<?=$subtitle['list']['participants']?>'
                                            }
                                            );
                                        }
                                        "
                                    >
                                    <?=htmlentities(_("List participants"))?>
                                </button>
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/list'
                                    onclick="
                                        if (!/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/.test(
                                            document.getElementById('raffleId').value.trim())
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid raffle identificator."))?>');
                                        } else {
                                            requestAndProcessPageJSONData(
                                                {
                                                    'collection':'raffle',
                                                    'action':'check',
                                                    'raffleid': document.getElementById('raffleId').value.trim(),
                                                    'subtitle': '<?=$subtitle['list']['winners']?>'
                                                }
                                            );
                                        }
                                        "
                                    >
                                    <?=htmlentities(_("List winners"))?>
                                </button>
                                <br />
                                <input name="comment" id="participantComment" size="50" type="text" placeholder="<?=_('Participant Comment (will be displayed publicly)')?>" />
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/join'
                                    onclick="
                                        if (!/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/.test(
                                            document.getElementById('raffleId').value.trim())
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid raffle identificator."))?>');
                                        } else {
                                            requestAndProcessPageJSONData(
                                                {
                                                    'collection':'raffle',
                                                    'action':'join',
                                                    'raffleid': document.getElementById('raffleId').value.trim(),
                                                    'subtitle': '<?=$subtitle['list']['result']?>'
                                                }
                                            )
                                        }
                                    "
                                    >
                                    <?=htmlentities(_("Join"))?>
                                </button>
                                <br />
                                <input name="limit" id="limit" type="text" placeholder="<?=_('How many winners?')?>" />
                                <button
                                    type='button'
                                    class='button'
                                    value='/raffle/raffle'
                                    onclick="
                                        if (!/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/.test(
                                            document.getElementById('raffleId').value.trim())
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid raffle identificator."))?>');
                                        } else if (
                                            !/^[0-9]+$/.test(
                                                document.getElementById('limit').value.trim()
                                            )
                                            &&
                                            document.getElementById('limit').value.trim()<1
                                        ){
                                            alert('<?=addslashes(_("You must provide a valid number of desired winners (>0)."))?>');
                                        } else {
                                            requestAndProcessPageJSONData(
                                                {
                                                    'collection':'raffle',
                                                    'action':'raffle',
                                                    'raffleid': document.getElementById('raffleId').value.trim(),
                                                    'limit': document.getElementById('limit').value.trim(),
                                                    'subtitle': '<?=$subtitle['list']['winners']?>'
                                                }
                                            )
                                        }
                                    "
                                    >
                                    <?= htmlentities(_('Raffle')) ?>
                                </button>
                            </div>
                        </div>
                    </form>
                <div id="dataTable" class="table-responsive table-container">
                </div>
                <div>
                    <?=htmlentities(_("Server script exec. time: "));?>
                    <span id="execTime"></span>
                    <?=htmlentities(_(" seconds (aprox.)"));?>
                </div>
                <br />
                <div class="page-footer">
                    <p class="text-center">
                        <small>
                            <?=htmlentities(_("Want to contribute to this project?"));?>
                            <a href="https://github.com/elcodedocle/gplusraffle">
                                <?=htmlentities(_("Fork me on github!!"));?>
                            </a>
                            <?=htmlentities(_("and/or"));?>
                            <?=htmlentities(_("Buy me a beer!!"));?>
                            (<a href="http://goo.gl/ql69W2">Paypal</a> -
                            <!--suppress HtmlUnknownTarget -->
                            <a href="bitcoin:13qZDBXVtUa3wBY99E7yQXBRGDMKpDmyxa">
                                Bitcoin: 13qZDBXVtUa3wBY99E7yQXBRGDMKpDmyxa
                            </a> -
                            <!--suppress HtmlUnknownTarget -->
                            <a href="dogecoin:D8ZE9piiaj3aMZeToqyBmUMctDMfmErJCd">
                                Dogecoin: D8ZE9piiaj3aMZeToqyBmUMctDMfmErJCd
                            </a>)
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>