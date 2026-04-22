<?php
namespace w3des\NewsletterBundle;

use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validation;
use w3des\NewsletterBundle\Entity\NewsletterEmail;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use w3des\AdminBundle\Service\Settings;
use Symfony\Component\Console\Output\OutputInterface;
use w3des\NewsletterBundle\Entity\NewsletterContent;

class Util
{
    protected $em;
    protected $stack;
    protected $router;
    protected $settings;
    protected $mailer;
    protected $defaultScheme;
    protected $defaultHost;

    public function __construct(EntityManager $em, RequestStack $stack, Router $router, Settings $settings, \Swift_Mailer $mailer, $defaultScheme, $defaultHost)
    {
        $this->settings = $settings;
        $this->em = $em;
        $this->stack = $stack;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->defaultHost = $defaultHost;
        $this->defaultScheme = $defaultScheme;
    }
    public function import( $pageLocale, $fileName)
    {
        $repo = $this->em->getRepository(NewsletterEmail::class);
        $fp = fopen($fileName, 'r');
        $count = 0;
        $lines = 0;
        $v = Validation::createValidator();
        $rules = [
            new NotBlank(),
            new Email()
        ];
        while($line = \fgetcsv($fp,null, ';')) {
            $lines ++;
            $email = trim($line[0]);
            if(count($v->validate($email, $rules)) == 0 && !$repo->findOneBy([
                'locale' => $pageLocale,
                'email' => $email
            ])) {
                $mail = new NewsletterEmail();
                $mail->setEmail($email);
                $mail->setCreatedAt(new \DateTime());
                $mail->setLocale($pageLocale);
                $mail->setId(Uuid::uuid4()->toString());
                $this->em->persist($mail);
                $this->em->flush();
                $count++;
            }

        }
        \fclose($fp);
        return ' Zaimportowano ' . $count . '/ ' . $lines.' nowych e-maili';
    }

    public function send(NewsletterContent $model, $testMail = null, OutputInterface $output = null)
    {
        $content = $this->fixContent($model->getContent());
        if ($testMail) {
            $mails = [$testMail];
        } else {
            $mails = [];
            foreach ($this->em->getRepository(NewsletterEmail::class)->findBy([
                'locale' => $model->getLocale()
            ], [
                'createdAt' => 'desc'
            ]) as $tmp) {
                $mails[] = $tmp->getEmail();
            }
        }
        $i = 0;
        $mailFrom = $this->settings->get('mail_from', null, $model->getLocale());
        $mailFromName = $this->settings->get('mail_from_name', null, $model->getLocale()) ?: $mailFrom;
        foreach ($mails as $mail) {
            try {
                if (!\Swift_Validate::email($mail)) {
                    continue;
                }
                $link = $this->router->generate('newsletter.subscribe', [
                    'email' => \base64_encode($mail),
                    'remove' => 1,
                    'direct' => 1
                ], UrlGeneratorInterface::ABSOLUTE_URL);
                $cnt = $content . '<br /><br />--<br /><p style="font-size: 11px;">Aby wypisać się z naszej listy dystrybucyjnej <a href="' . $link . '" target="_blank"><span style="color:blue">kliknij tutaj</span></a></p>';

                $msg = \Swift_Message::newInstance($model->getTitle(), $cnt, 'text/html', 'utf-8');
                $msg->setFrom($mailFrom, $mailFromName);
                $msg->setReplyTo($mailFrom);
                $msg->setReturnPath($mailFrom);
                // $this->getHeaders()->addMailboxHeader('Rcp-To', [$this->get('settings')->get('mail_from') => $this->get('settings')->get('mail_from')]);
                $msg->setTo(trim($mail));

                if ($output) {
                    $output->writeln('<comment>' . $mail . ';</comment>');
                }
                $this->mailer->send($msg);
                $i++;
                if ($i % 100 == 0) {
                    if ($output) {
                        $output->writeln('<info>Sleep</info>');
                    }
                    //sleep(60);
                }
            } catch(\Exception $e1) {

            } catch(\Throwable $e2) {

            }
        }

    }

    protected function fixContent($cnt)
    {
        $request = $this->stack->getCurrentRequest();
        $prefix = '';
        if ($request) {
            $prefix = $request->getScheme() . '://' . $request->getHost() . '/';
        } else {
            $prefix = $this->defaultScheme . '://' . $this->defaultHost . '/';
        }

        $cnt = preg_replace('#src="/#i', 'src="' . $prefix, $cnt);
        $cnt = preg_replace('#href="/#i', 'href="' . $prefix, $cnt);

        return $cnt;
    }

}

