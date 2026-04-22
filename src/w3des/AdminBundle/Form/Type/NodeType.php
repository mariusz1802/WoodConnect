<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\HttpFoundation\RequestStack;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Service\Nodes;

class NodeType extends AbstractType
{

    protected $em;

    protected $opt;

    protected $stack;
    protected $nodes;

    public function __construct(EntityManager $em, RequestStack $stack, Nodes $nodes)
    {
        $this->em = $em;
        $this->stack = $stack;
        $this->nodes = $nodes;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->opt = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('type');
        $resolver->setDefault('choices_as_values', true);
        $resolver->setDefault('choice_loader', new CallbackChoiceLoader(function () {
            $result = [];
            foreach ($this->em->getRepository(Node::class)
                ->findBy([
                'type' => $this->opt['type'],
                'locale' => $this->stack->getMasterRequest()->attributes->get('pageLocale')
            ], [
                'pos' => 'asc'
            ]) as $item) {
                $result[$this->nodes->getVariable($item, isset($this->opt['title']) ? $this->opt['title']: 'title') . ' (' . $item->getId() . ')'] = $item->getId();
            }

            return $result;
        }));

    }
}

