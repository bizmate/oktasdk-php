<?php

namespace Okta\Resource;

/**
 * Implementation of the Okta Authentication resource, access via $okta->auth
 *
 * http://developer.okta.com/docs/api/resources/authn.html
 */
class Authentication extends Base
{
    /**
     * Every authentication transaction starts with primary authentication which
     * validates a user's primary password credential. Password Policy, MFA
     * Policy, and Sign-On Policy is evaluated during primary authentication to
     * determine if the user's password is expired, a factor should be enrolled,
     * or additional verification is required.
     *
     * @param  string $username   User's non-qualified short-name or unique
     *                            fully-qualified login
     * @param  string $password   User's password credential
     * @param  string $relayState Optional state value that is persisted for the
     *                            lifetime of the authentication transaction
     * @param  array  $options    Array of opt-in features for the
     *                            authentication transaction
     * @param  array  $context    Array of additional context properties for the
     *                            authentication transaction
     *
     * @return object             Authentication Transaction Object
     */
    public function authn($username, $password, $relayState = null, array $options = null, array $context = null)
    {
        $request = $this->request->post('authn');

        $request->data([
            'username' => $username,
            'password' => $password
        ]);

        if (isset($relayState)) $request->data(['relayState' => $relayState]);
        if (isset($options))    $request->data(['options' => $options]);
        if (isset($context))    $request->data(['context' => $context]);

        return $request->send();
    }

    /**
     * This operation changes a user's password by providing the existing
     * password and the new password password for authentication transactions
     * with either the PASSWORD_EXPIRED or PASSWORD_WARN state.
     *
     *   - A user must change their expired password for an authentication
     *     transaction with PASSWORD_EXPIRED status to successfully complete the
     *     transaction.
     *
     *   - A user may opt-out of changing their password (skip) when the
     *     transaction has a PASSWORD_WARN status
     *
     * @param  string $stateToken  State token for current transaction
     * @param  string $oldPassword User's current password that is expired or
     *                             about to expire
     * @param  string $newPassword New password for user
     *
     * @return object              Authentication Transaction Object
     */
    public function changePassword($stateToken, $oldPassword, $newPassword)
    {
        $request = $this->request->post('authn/credentials/change_password');

        $request->data([
            'stateToken'  => $stateToken,
            'oldPassword' => $oldPassword,
            'newPassword' => $newPassword
        ]);

        return $request->send();
    }

    /**
     * Enrolls a user with a factor assigned by their MFA Policy.
     *
     * @param  string $stateToken State token for current transaction
     * @param  string $factorType Type of factor
     * @param  string $provider   Factor provider
     * @param  array  $profile    Associative array containing profile
     *                            key/values of a supported factor
     *
     * @return object             Authentication Transaction Object
     */
    public function enrollFactor($stateToken, $factorType, $provider, array $profile)
    {
        $request = $this->request->post('authn/factors');

        $request->data([
            'stateToken' => $stateToken,
            'factorType' => $factorType,
            'provider'   => $provider,
            'profile'    => $profile
        ]);

        return $request->send();
    }

    /**
     * Activates a factor by verifying the OTP.
     *
     * @param  string $stateToken State token for current transaction
     * @param  string $fid        ID of factor returned from enrollment
     * @param  string $passCode   OTP generated by device
     *
     * @return object             Authentication Transaction Object
     */
    public function activateFactor($stateToken, $fid, $passCode)
    {
        $request = $this->request->post('authn/factors/' . $fid . '/lifecycle/activate');

        $request->data([
            'stateToken' => $stateToken,
            'passCode'   => $passCode
        ]);

        return $request->send();
    }

    /**
     * Verifies an enrolled factor for an authentication transaction with the
     * MFA_REQUIRED or MFA_CHALLENGE state
     *
     * @param  string $stateToken   State token for current transaction
     * @param  string $fid          ID of factor returned from enrollment
     * @param  array  $verification Array of verification properties
     *
     * @return object               Authentication Transaction Object
     */
    public function verifyFactor($stateToken, $fid, array $verification)
    {
        $request = $this->request->post('authn/factors/' . $fid . '/verify');

        $request->data(['stateToken' => $stateToken]);
        $request->data($verification);

        return $request->send();
    }

    /**
     * Convinience method for verifying an answer to a question factor.
     *
     * @param  string $stateToken State token for current transaction
     * @param  string $fid        ID of factor returned from enrollment
     * @param  string $answer     Answer to security question
     *
     * @return object             Authentication Transaction Object
     */
    public function verifySecuirtyQuestionFactor($stateToken, $fid, $answer)
    {
        $this->verifyFactor($stateToken, $fid, ['answer' => $answer]);
    }

    /**
     * Convinience method for verifying a pass code from an SMS factor.
     *
     * @param  string $stateToken State token for current transaction
     * @param  string $fid        ID of factor returned from enrollment
     * @param  string $passCode   OTP sent to device
     *
     * @return object             Authentication Transaction Object
     */
    public function verifySmsFactor($stateToken, $fid, $passCode)
    {
        $this->verifyFactor($stateToken, $fid, ['passCode' => $passCode]);
    }

    /**
     * Convinience method for verifying an OTP for a token:software:totp factor.
     *
     * @param  string $stateToken State token for current transaction
     * @param  string $fid        ID of factor returned from enrollment
     * @param  string $passCode   OTP sent to device
     *
     * @return object             Authentication Transaction Object
     */
    public function verifyTotpFactor($stateToken, $fid, $passCode)
    {
        $this->verifyFactor($stateToken, $fid, ['passCode' => $passCode]);
    }

    /**
     * Convienience mthod for sending an SMS challenge to a user's device
     *
     * @param  string $stateToken State token for current transaction
     * @param  string $fid        ID of factor returned from enrollment
     *
     * @return object             Authentication Transaction Object
     */
    public function sendSmsChallenge($stateToken, $fid)
    {
        return $this->verifyFactor($stateToken, $fid, []);
    }

    /**
     * Resends an SMS challenge to a user's device
     *
     * @param  string $stateToken State token for current transaction
     * @param  string $fid        ID of factor returned from enrollment
     *
     * @return object             Authentication Transaction Object
     */
    public function resendSmsChallenge($stateToken, $fid)
    {
        $request = $this->request->post('authn/factors/' . $fid . '/verify/resend');

        $request->data(['stateToken' => $stateToken]);

        return $request->send();
    }

    /**
     * Starts a new password recovery transaction for a given user and issues a
     * recovery token that can be used to reset a user's password.
     *
     * @param  string $username   User's non-qualified short-name or unique
     *                            fully-qualified login
     * @param  string $factorType Recovery factor to use for primary
     *                            authentication (EMAIL or SMS)
     * @param  string $relayState Optional state value that is persisted for the
     *                            lifetime of the recovery transaction
     *
     * @return object             Recovery Transaction Object
     */
    public function forgotPassword($username, $factorType = null, $relayState = null)
    {
        $request = $this->request->post('authn/recovery/password');

        $request->data(['username' => $username]);

        if (isset($factorType)) $request->data(['factorType' => $factorType]);
        if (isset($relayState)) $request->data(['relayState' => $relayState]);

        return $request->send();
    }

    /**
     * Starts a new unlock recovery transaction for a given user and issues a
     * recovery token that can be used to unlock a user's account.
     *
     * @param  string $username   User's non-qualified short-name or unique
     *                            fully-qualified login
     * @param  string $factorType Recovery factor to use for primary
     *                            authentication (EMAIL or SMS)
     * @param  string $relayState Optional state value that is persisted for the
     *                            lifetime of the recovery transaction
     *
     * @return object             Recovery Transaction Object
     */
    public function unlockAccount($username, $factorType = null, $relayState = null)
    {
        $request = $this->request->post('authn/recovery/unlock');

        $request->data(['username' => $username]);

        if (isset($factorType)) $request->data(['factorType' => $factorType]);
        if (isset($relayState)) $request->data(['relayState' => $relayState]);

        return $request->send();
    }

    /**
     * Verifies a SMS OTP (passCode) sent to the user's mobile phone for primary
     * authentication for a recovery transaction with RECOVERY_CHALLENGE status.
     *
     * @param  string $stateToken State token for current recovery transaction
     * @param  string $passCode   OTP sent to device
     *
     * @return object             Recovery Transaction Object
     */
    public function verifySmsRecoveryFactor($stateToken, $passCode)
    {
        $request = $this->request->post('authn/recovery/factors/sms/verify');

        $request->data([
            'stateToken' => $stateToken,
            'passCode'   => $passCode
        ]);

        return $request->send();
    }

    /**
     * Validates a recovery token that was distributed to the end-user to
     * continue the recovery transaction.
     *
     * @param  string $recoveryToken Recovery token that was distributed to
     *                               end-user via out-of-band mechanism such as email
     *
     * @return object                Recovery Transaction Object
     */
    public function verifyRecoveryToken($recoveryToken)
    {
        $request = $this->request->post('authn/recovery/token');

        $request->data(['recoveryToken' => $recoveryToken]);

        return $request->send();
    }

    /**
     * Answers the user's recovery question to ensure only the end-user redeemed
     * the recovery token for recovery transaction with a RECOVERY status.
     *
     * @param  string $stateToken State token for current transaction
     * @param  string $answer     Answer to user's recovery question
     *
     * @return object             Recovery Transaction Object
     */
    public function answerRecoveryQuestion($stateToken, $answer)
    {
        $request = $this->request->post('authn/recovery/answer');

        $request->data([
            'stateToken' => $stateToken,
            'answer'     => $answer
        ]);

        return $request->send();
    }

    /**
     * Resets a user's password to complete a recovery transaction with a
     * PASSWORD_RESET state.
     *
     * @param  string $stateToken  State token for current transaction
     * @param  string $newPassword User's new password
     *
     * @return object              Recovery Transaction Object
     */
    public function resetPassword($stateToken, $newPassword)
    {
        $request = $this->request->post('authn/credentials/reset_password');

        $request->data([
            'stateToken'  => $stateToken,
            'newPassword' => $newPassword
        ]);

        return $request->send();
    }

    /**
     * Retrieves the current transaction state for a state token.
     *
     * @param  string $stateToken State token for a transaction
     *
     * @return object              Recovery Transaction Object
     */
    public function getState($stateToken)
    {
        $request = $this->request->post('authn');

        $request->data(['stateToken' => $stateToken]);

        return $request->send();
    }

    /**
     * Moves the current transaction state back to the previous state.
     *
     * @param  string $stateToken State token for a transaction
     *
     * @return object              Recovery Transaction Object
     */
    public function previousState($stateToken)
    {
        $request = $this->request->post('authn/previous');

        $request->data(['stateToken' => $stateToken]);

        return $request->send();
    }

    /**
     * Moves the current transaction state back to the previous state. This
     * operation is only available for MFA_ENROLL or PASSWORD_WARN states when
     * published as a link
     *
     * @param  string $stateToken State token for a transaction
     *
     * @return object              Recovery Transaction Object
     */
    public function skipState($stateToken)
    {
        $request = $this->request->post('authn/skip');

        $request->data(['stateToken' => $stateToken]);

        return $request->send();
    }

    /**
     * Cancels the current transaction and revokes the state token.
     *
     * @param  string $stateToken State token for a transaction
     *
     * @return object             Empty object or object containing optional
     *                            state value that was persisted for the
     *                            authentication or recovery transaction
     */
    public function cancel($stateToken)
    {
        $request = $this->request->post('authn/cancel');

        $request->data(['stateToken' => $stateToken]);

        return $request->send();
    }
}
