<?php

namespace Tests\ConditionalConstraints;

use ComparisonConstraints\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

class ComparisonConstraintsTest extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function setUp()
    {
        $builder = new ValidatorBuilder();
        $builder->enableAnnotationMapping();
        $this->validator = $builder->getValidator();
    }

    public function testExpressionViolation()
    {
        $event = new Event();
        $event->setStartDate(new \DateTime('today'));
        $event->setEndDate(new \DateTime('yesterday'));

        $this->assertViolations(
            [
                'endDate' => 'This value is not valid.',
            ],
            $this->validator->validate($event)
        );
    }

    private function assertViolations(array $expected, ConstraintViolationListInterface $violationList)
    {
        $violations = [];
        foreach ($violationList as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $violations[$violation->getPropertyPath()] = $violation->getMessage();
        }

        $this->assertSame($expected, $violations);
    }
}
