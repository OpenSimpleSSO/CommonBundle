<?php

namespace SimpleSSO\CommonBundle\Model;

use LogicException;
use SimpleSSO\CommonBundle\Exception\ApiBadRequestException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FormModel
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * FormModel constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Hydrate the bad request error details into a form.
     *
     * @param ApiBadRequestException $exception
     * @param FormInterface          $form
     * @param string                 $translationPrefix
     */
    public function hydrateBadRequestInForm(ApiBadRequestException $exception, FormInterface $form, string $translationPrefix = '')
    {
        if ($exception->getStatus() === 400 || $exception->getStatus() === 422) { // BadRequest or UnprocessableEntity
            foreach ($exception->getDetails() as $attribute => $errors) {
                $target = $form->has($attribute) ? $form->get($attribute) : $form;
                switch (true) {
                    case is_string($errors):
                        $translationKey = $translationPrefix . $errors;
                        $target->addError(new FormError(
                            $this->translator->trans($translationKey, [], 'validators'),
                            $translationKey
                        ));
                        break;

                    case is_array($errors):
                        foreach ($errors as $error) {
                            switch (true) {
                                case is_string($error):
                                    $translationKey = $translationPrefix . $error;
                                    $target->addError(new FormError(
                                        $this->translator->trans($translationKey, [], 'validators'),
                                        $translationKey
                                    ));
                                    break;

                                case is_array($error) && key_exists('key', $error):
                                    $translationKey = $translationPrefix . $error['key'];
                                    $parameters = $error['params'] ?? $error['parameters'] ?? [];
                                    $target->addError(new FormError(
                                        $this->translator->trans($translationKey, $parameters, 'validators'),
                                        $translationKey,
                                        $parameters
                                    ));
                                    break;

                                default:
                                    throw new LogicException('Unknown error format.');
                            }
                        }
                        break;

                    default:
                        throw new LogicException('Unknown error format.');
                }
            }
        } else {
            $translationKey = $translationPrefix . 'error.' . $exception->getStatus();
            $form->addError(new FormError(
                $this->translator->trans($translationKey, [
                    '%message%' => $exception->getMessage(),
                ], 'validators'),
                $translationKey
            ));
        }
    }
}
