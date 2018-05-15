<?php

namespace SilverStripe\AssetAdmin\Tests\Behat\Context;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use SilverStripe\BehatExtension\Context\SilverStripeContext;

class FeatureContext extends SilverStripeContext
{
    /**
     * Grab the JavaScript errors from the session. Only works in companion
     * with a global window variable `errors` that contains the JavaScript
     * and/or XHR errors.
     *
     * @AfterStep
     */
    public function takeJSErrorsAfterFailedStep(AfterStepScope $event)
    {
        $code = $event->getTestResult()->getResultCode();
        $driver = $this->getSession()->getDriver();

        if ($driver instanceof Selenium2Driver && $code === 99) {
            // Fetch errors from window variable.
            try {
                $json = $this->getSession()->evaluateScript("return JSON.stringify(window.errors);");
            } catch (\Exception $e) {
                // Ignore this exception, because this may be caused by the
                // driver and/or JavaScript.
                return;
            }

            // Unserialize the errors.
            $errors = json_decode($json);

            if (json_last_error() == JSON_ERROR_NONE) {
                $messages = [];
                if (empty($messages)) {
                    echo "NO JAVASCRIPT ERRORS!\n\n";
                }
                foreach ($errors as $error) {
                    if ($error->type == "javascript") {
                        $messages[] = "- {$error->message} ({$error->location})";
                    } elseif ($error->type == "xhr") {
                        $messages[] = "- {$error->message} ({$error->method} {$error->url}): {$error->statusCode} {$error->response}";
                    }
                }

                printf("JavaScript errors:\n\n" . implode("\n", $messages));
            }
        }
    }
}
