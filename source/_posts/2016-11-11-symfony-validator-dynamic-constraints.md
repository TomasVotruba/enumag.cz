---
title: "How to do Dynamic Constraints in Symfony/Validator"
categories:
    - Symfony
perex: Some edge-cases with Symfony Validator might force you to create a constraint dynamically during the validation. This article will show you how to do it and how to solve error mapping for such constraints.
thumbnail: symfony.png
lang: en
---

<p class="perex">{{ page.perex|raw }}</p>

Use case
----

You have an Address entity with a country and zipcode. There is a [ZipCode constraint](https://github.com/Soullivaneuh/IsoCodesValidator/blob/master/src/Constraints/ZipCode.php) available but requires you to specify the country in options which you cannot do in annotation because country is another field of Address entity.

```language-php
use SLLH\IsoCodesValidator\Constraints\ZipCode;
use Symfony\Component\Validator\Constraints as Assert;

class Address
{
    // street, city, etc.

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Country()
     */
    protected $country;

    /**
     * @todo Validate this field based on the country specified in $this->country.
     * @var string
     * @Assert\NotBlank()
     * @ZipCode(country = "???")
     */
    protected $zipcode;

    // getters and setters
}
```

Solution
----

Well it's of course impossible to simply fix the code above by replacing the question marks with something. So we need another approach. The way to go in this case is of course the Callback constraint.

```language-php
use SLLH\IsoCodesValidator\Constraints\ZipCode;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Address
{
    // same as above

    /**
     * @Assert\Callback()
     */
    public function validateZipcode(ExecutionContextInterface $context)
    {
        $constraint = new ZipCode(['country' => $this->country]);
        $violations = $context->getValidator()->validate($this->zipcode, $constraint);

        foreach ($violations as $violation) {
            $context->getViolations()->add($violation);
        }
    }
}
```

Now this is almost correct except for one flaw. If the zipcode is not valid the violation is not attached to the zipcode field but to the Address class instead. Fortunately there is a way to solve this problem as well.

```language-php
use SLLH\IsoCodesValidator\Constraints\ZipCode;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Address
{
    // same as above

    /**
     * @Assert\Callback()
     */
    public function validateZipcode(ExecutionContextInterface $context)
    {
        $constraint = new ZipCode(['country' => $this->country]);
        $validator = $context->getValidator()->inContext($context);
        $validator->atPath('zipcode')->validate($this->zipcode, $constraint);
    }
}
```

And that's it! The violation will be added to the field directly in the intended context too so there is no need to duplicate it.

This approach should solve most of the advanced use-cases you might have. Along with the tips from my previous articles it makes Symfony/Validator a really powerful validation tool.

Usage with Nette Framework
----

If you want to use the Symfony/Validator component in your Nette application you will need [Kdyby/Validator](https://github.com/Kdyby/Validator).
