<?php


namespace App\Service;


use Sylius\Component\Resource\Generator\RandomnessGeneratorInterface;
use Sylius\Component\User\Security\Checker\UniquenessCheckerInterface;
use Sylius\Component\User\Security\Generator\GeneratorInterface;
use Webmozart\Assert\Assert;

final class UniqueTokenGeneratorExtended implements GeneratorInterface
{
    /** @var RandomnessGeneratorInterface */
    private $generator;

    /** @var UniquenessCheckerInterface */
    private $uniquenessChecker;

    /** @var int */
    private $tokenLength;

    /**
     * @param RandomnessGeneratorInterface $generator
     * @param UniquenessCheckerInterface $uniquenessChecker
     * @param int $tokenLength
     */
    public function __construct(
        RandomnessGeneratorInterface $generator,
        UniquenessCheckerInterface $uniquenessChecker,
        int $tokenLength
    ) {
        Assert::greaterThanEq($tokenLength, 1, 'The value of token length has to be at least 1.');

        $this->generator = $generator;
        $this->tokenLength = $tokenLength;
        $this->uniquenessChecker = $uniquenessChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        do {
            $token = $this->generator->generateUriSafeString($this->tokenLength);
        } while (!$this->uniquenessChecker->isUnique($token));

        return $token;
    }
}
