<?php


function getTypeOfRequest(array $request): string
{
    $isMessage = $request['message'] ?? false;
    $isCallback = $request['callback_query'] ?? false;
    // $isMyChatMember = $request['my_chat_member'] ?? false;
    // $isEditedMessage = $request['edited_message'] ?? false;
    // $isInlineQuery = $request['inline_query'] ?? false;
    // $isPoll = $request['poll'] ?? false;
    // $isPollAnswer = $request['poll_answer'] ?? false;
    // $chosenInlineResult = $request['chosen_inline_result'] ?? false;

    if($isMessage) {
        return 'message';
    } elseif($isCallback) {
        return 'callback_query';
    }

    // if ($isMyChatMember) {
    //     return 'my_chat_member';
    // } elseif($isEditedMessage) {
    //     return 'edited_message';
    // } elseif($isInlineQuery) {
    //     return 'inline_query';
    // } elseif($isPoll) {
    //     return 'poll';
    // } elseif($isPollAnswer) {
    //     return 'poll_answer';
    // } elseif($chosenInlineResult) {
    //     return 'chosen_inline_result';
    // }

    return 'not handled';
}