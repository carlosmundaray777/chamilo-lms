<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CourseBundle\Entity;

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * CItemProperty
 *
 * @ORM\Table(name="c_item_property", indexes={@ORM\Index(name="idx_item_property_toolref", columns={"tool", "ref"})})
 * @ORM\Entity(repositoryClass="Chamilo\CoreBundle\Entity\Repository\ItemPropertyRepository")
 */
class CItemProperty
{
    /**
     * @var integer
     *
     * @ORM\Column(name="iid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $iid;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=true)
     */
    private $id;

    /** //, inversedBy="users",
     * @ORM\ManyToOne(targetEntity="Chamilo\CoreBundle\Entity\Course", cascade={"persist"})
     * @ORM\JoinColumn(name="c_id", referencedColumnName="id")
     */
    protected $course;

    /**
     * @var string
     *
     * @ORM\Column(name="tool", type="string", length=100, nullable=false)
     */
    private $tool;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insert_date", type="datetime", nullable=false)
     */
    private $insertDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastedit_date", type="datetime", nullable=false)
     */
    private $lasteditDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="ref", type="integer", nullable=false)
     */
    private $ref;

    /**
     * @var string
     *
     * @ORM\Column(name="lastedit_type", type="string", length=100, nullable=false)
     */
    private $lasteditType;

    /**
     * @var integer
     *
     * @ORM\Column(name="lastedit_user_id", type="integer", nullable=false)
     */
    private $lasteditUserId;

    /** //, inversedBy="users",
     * @ORM\ManyToOne(targetEntity="Chamilo\CourseBundle\Entity\CGroupInfo", cascade={"persist"})
     * @ORM\JoinColumn(name="to_group_id", referencedColumnName="iid")
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="Chamilo\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="to_user_id", referencedColumnName="id")
     */
    protected $toUser;

    /**
     * @ORM\ManyToOne(targetEntity="Chamilo\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="insert_user_id", referencedColumnName="id")
     */
    protected $insertUser;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visibility", type="boolean", nullable=false)
     */
    private $visibility;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_visible", type="datetime", nullable=true)
     */
    private $startVisible;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_visible", type="datetime", nullable=true)
     */
    private $endVisible;

    /** //, inversedBy="users",
     * @ORM\ManyToOne(targetEntity="Chamilo\CoreBundle\Entity\Session", cascade={"persist"})
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     */
    protected $session;

    /**
     * CItemProperty constructor.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
        $this->insertDate = new \DateTime();
        $this->lasteditDate = new \DateTime();
    }

    /**
     * Set tool
     *
     * @param string $tool
     *
     * @return CItemProperty
     */
    public function setTool($tool)
    {
        $this->tool = $tool;

        return $this;
    }

    /**
     * Get tool
     *
     * @return string
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * Set insertDate
     *
     * @param \DateTime $insertDate
     *
     * @return CItemProperty
     */
    public function setInsertDate($insertDate)
    {
        $this->insertDate = $insertDate;

        return $this;
    }

    /**
     * Get insertDate
     *
     * @return \DateTime
     */
    public function getInsertDate()
    {
        return $this->insertDate;
    }

    /**
     * Set lasteditDate
     *
     * @param \DateTime $lasteditDate
     *
     * @return CItemProperty
     */
    public function setLasteditDate($lasteditDate)
    {
        $this->lasteditDate = $lasteditDate;

        return $this;
    }

    /**
     * Get lasteditDate
     *
     * @return \DateTime
     */
    public function getLasteditDate()
    {
        return $this->lasteditDate;
    }

    /**
     * Set ref
     *
     * @param integer $ref
     *
     * @return CItemProperty
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get ref
     *
     * @return integer
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set lasteditType
     *
     * @param string $lasteditType
     *
     * @return CItemProperty
     */
    public function setLasteditType($lasteditType)
    {
        $this->lasteditType = $lasteditType;

        return $this;
    }

    /**
     * Get lasteditType
     *
     * @return string
     */
    public function getLasteditType()
    {
        return $this->lasteditType;
    }

    /**
     * Set lasteditUserId
     *
     * @param integer $lasteditUserId
     *
     * @return CItemProperty
     */
    public function setLasteditUserId($lasteditUserId)
    {
        $this->lasteditUserId = $lasteditUserId;

        return $this;
    }

    /**
     * Get lasteditUserId
     *
     * @return integer
     */
    public function getLasteditUserId()
    {
        return $this->lasteditUserId;
    }

    /**
     * Set visibility
     *
     * @param boolean $visibility
     *
     * @return CItemProperty
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility
     *
     * @return boolean
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set startVisible
     *
     * @param \DateTime $startVisible
     *
     * @return CItemProperty
     */
    public function setStartVisible($startVisible)
    {
        $this->startVisible = $startVisible;

        return $this;
    }

    /**
     * Get startVisible
     *
     * @return \DateTime
     */
    public function getStartVisible()
    {
        return $this->startVisible;
    }

    /**
     * Set endVisible
     *
     * @param \DateTime $endVisible
     *
     * @return CItemProperty
     */
    public function setEndVisible($endVisible)
    {
        $this->endVisible = $endVisible;

        return $this;
    }

    /**
     * Get endVisible
     *
     * @return \DateTime
     */
    public function getEndVisible()
    {
        return $this->endVisible;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return CItemProperty
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     *
     * @return CItemProperty
     */
    public function setSession($session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param Course $course
     *
     * @return CItemProperty
     */
    public function setCourse($course)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * @return CgroupInfo
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param CgroupInfo $group
     *
     * @return CItemProperty
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return User
     */
    public function getToUser()
    {
        return $this->toUser;
    }

    /**
     * @param User $toUser
     *
     * @return $this
     */
    public function setToUser($toUser)
    {
        $this->toUser = $toUser;

        return $this;
    }

    /**
     * @return User
     */
    public function getInsertUser()
    {
        return $this->insertUser;
    }

    /**
     * @param User $insertUser
     *
     * @return $this
     */
    public function setInsertUser(User $insertUser)
    {
        $this->insertUser = $insertUser;
        $this->lasteditUserId = $insertUser->getId();

        return $this;
    }
}
