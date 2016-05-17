<?php
/* For licensing terms, see /license.txt */

/**
 * Class TestCategory
 * @author hubert.borderiou
 * @author Julio Montoya - several fixes
 * @todo rename to ExerciseCategory
 */
class TestCategory
{
    public $id;
    public $name;
    public $description;

	/**
	 * Constructor of the class Category
	 * If you give an in_id and no in_name, you get info concerning the category of id=in_id
	 * otherwise, you've got an category objet avec your in_id, in_name, in_descr
	 *
     * @param int    $id
     * @param string $name
     * @param string $description
     *
     * @author - Hubert Borderiou
     */
    public function __construct($id = 0, $name = '', $description = "")
    {
        if ($id != 0 && $name == "") {
            $obj = new TestCategory();
            $obj->getCategory($id);
            $this->id = $obj->id;
            $this->name = $obj->name;
            $this->description = $obj->description;
        } else {
            $this->id = $id;
            $this->name = $name;
            $this->description = $description;
        }
    }

    /**
     * return the TestCategory object with id=in_id
     * @param int $id
     *
     * @return TestCategory
     */
    public function getCategory($id)
    {
        $table = Database::get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
        $id = intval($id);
        $sql = "SELECT * FROM $table
                WHERE id = $id AND c_id=".api_get_course_int_id();
        $res = Database::query($sql);

        if (Database::num_rows($res)) {
            $row = Database::fetch_array($res);
            $this->id = $row['id'];
            $this->name = $row['title'];
            $this->description  = $row['description'];
        }
    }

	/**
     * add TestCategory in the database if name doesn't already exists
	 */
    public function addCategoryInBDD()
    {
        $table = Database :: get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
        $v_name = $this->name;
        $v_name = Database::escape_string($v_name);
        $v_description = $this->description;
        $v_description = Database::escape_string($v_description);
        // check if name already exists
        $sql = "SELECT count(*) AS nb FROM $table
                WHERE title = '$v_name' AND c_id=".api_get_course_int_id();
        $result_verif = Database::query($sql);
        $data_verif = Database::fetch_array($result_verif);
        // lets add in BDD if not the same name
        if ($data_verif['nb'] <= 0) {
            $c_id = api_get_course_int_id();
            $params = [
                'c_id' => $c_id,
                'title' => $v_name,
                'description' => $v_description,
            ];
            $new_id = Database::insert($table, $params);

            if ($new_id) {

                $sql = "UPDATE $table SET id = iid WHERE iid = $new_id";
                Database::query($sql);

                // add test_category in item_property table
                $course_id = api_get_course_int_id();
                $course_info = api_get_course_info_by_id($course_id);
                api_item_property_update(
                    $course_info,
                    TOOL_TEST_CATEGORY,
                    $new_id,
                    'TestCategoryAdded',
                    api_get_user_id()
                );
            }

            return $new_id;
        } else {

            return false;
        }
	}

	/**
     * Removes the category from the database
     * if there were question in this category, the link between question and category is removed
	 */
    public function removeCategory()
    {
        $table = Database :: get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
        $tbl_question_rel_cat = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_CATEGORY);
        $v_id = intval($this->id);
        $course_id = api_get_course_int_id();

        $sql = "DELETE FROM $table
                WHERE id= $v_id AND c_id=".$course_id;
        $result = Database::query($sql);
        if (Database::affected_rows($result) <= 0) {
            return false;
        } else {
            // remove link between question and category
            $sql2 = "DELETE FROM $tbl_question_rel_cat
                     WHERE category_id = $v_id AND c_id=".$course_id;
            Database::query($sql2);
            // item_property update
            $course_info = api_get_course_info_by_id($course_id);
            api_item_property_update(
                $course_info,
                TOOL_TEST_CATEGORY,
                $this->id,
                'TestCategoryDeleted',
                api_get_user_id()
            );

            return true;
        }
	}

	/**
     * Modify category name or description of category with id=in_id
	 */
    public function modifyCategory()
    {
        $table = Database :: get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
        $v_id = intval($this->id);
        $v_name = Database::escape_string($this->name);
        $v_description = Database::escape_string($this->description);
        $sql = "UPDATE $table SET
                title = '$v_name',
                description = '$v_description'
                WHERE id = $v_id AND c_id=".api_get_course_int_id();
        $result = Database::query($sql);
        if (Database::affected_rows($result) <= 0) {
            return false;
        } else {
            // item_property update
            $course_id = api_get_course_int_id();
            $course_info = api_get_course_info_by_id($course_id);
            api_item_property_update(
                $course_info,
                TOOL_TEST_CATEGORY,
                $this->id,
                'TestCategoryModified',
                api_get_user_id()
            );

            return true;
        }
	}

	/**
     * Gets the number of question of category id=in_id
	 */
    public function getCategoryQuestionsNumber()
    {
		$table = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_CATEGORY);
		$in_id = intval($this->id);
		$sql = "SELECT count(*) AS nb
		        FROM $table
		        WHERE category_id=$in_id AND c_id=".api_get_course_int_id();
		$res = Database::query($sql);
		$row = Database::fetch_array($res);

		return $row['nb'];
	}

    /**
     * @param string $in_color
     */
    public function display($in_color="#E0EBF5")
    {
		echo "<textarea style='background-color:$in_color; width:60%; height:100px;'>";
		print_r($this);
		echo "</textarea>";
	}

	/**
     * Return an array of all Category objects in the database
	 * If in_field=="" Return an array of all category objects in the database
	 * Otherwise, return an array of all in_field value
	 * in the database (in_field = id or name or description)
	 */
    public static function getCategoryListInfo($in_field = "", $courseId = "")
    {
        if (empty($courseId) || $courseId=="") {
            $courseId = api_get_course_int_id();
        }
        $table = Database :: get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
        $in_field = Database::escape_string($in_field);
        $tabres = array();
        if ($in_field == "") {
            $sql = "SELECT * FROM $table
                    WHERE c_id=$courseId ORDER BY title ASC";
            $res = Database::query($sql);
            while ($row = Database::fetch_array($res)) {
                $tmpcat = new TestCategory(
                    $row['id'],
                    $row['title'],
                    $row['description']
                );
                $tabres[] = $tmpcat;
            }
        } else {
            $sql = "SELECT $in_field FROM $table
                    WHERE c_id = $courseId
                    ORDER BY $in_field ASC";
            $res = Database::query($sql);
            while ($row = Database::fetch_array($res)) {
                $tabres[] = $row[$in_field];
            }
        }

		return $tabres;
	}

    /**
     * Return the TestCategory id for question with question_id = $questionId
     * In this version, a question has only 1 TestCategory.
     * Return the TestCategory id, 0 if none
     * @param int $questionId
     * @param int $courseId
     *
     * @return int
     */
	public static function getCategoryForQuestion($questionId, $courseId ="")
    {
		$result = 0;
        if (empty($courseId) || $courseId == "") {
            $courseId = api_get_course_int_id();
        }
		$table = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_CATEGORY);
        $questionId = intval($questionId);
		$sql = "SELECT category_id
		        FROM $table
		        WHERE question_id = $questionId AND c_id = $courseId";
		$res = Database::query($sql);
		if (Database::num_rows($res) > 0) {
            $data = Database::fetch_array($res);
			$result = $data['category_id'];
		}

		return $result;
	}

	/**
	 * true if question id has a category
	 */
	public static function isQuestionHasCategory($questionId)
    {
		if (TestCategory::getCategoryForQuestion($questionId) > 0) {
			return true;
		}
		return false;
	}

	/**
	 Return the category name for question with question_id = $questionId
	 In this version, a question has only 1 category.
	 Return the category id, "" if none
	 */
    public static function getCategoryNameForQuestion(
        $questionId,
        $courseId = ""
    ) {
		if (empty($courseId) || $courseId=="") {
			$courseId = api_get_course_int_id();
		}
		$catid = TestCategory::getCategoryForQuestion($questionId, $courseId);
		$result = "";	// result
		$table = Database::get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
		$catid = intval($catid);
		$sql = "SELECT title FROM $table
		        WHERE id = $catid  AND c_id = $courseId";
		$res = Database::query($sql);
		$data = Database::fetch_array($res);
		if (Database::num_rows($res) > 0) {
			$result = $data['title'];
		}

		return $result;
	}

	/**
	 * Return the list of differents categories ID for a test in the current course
	 * input : test_id
	 * return : array of category id (integer)
	 * hubert.borderiou 07-04-2011
	 * @param int $exerciseId
	 */
	public static function getListOfCategoriesIDForTest($exerciseId)
    {
		// parcourir les questions d'un test, recup les categories uniques dans un tableau
		$exercise = new Exercise();
		$exercise->read($exerciseId, false);
		$categoriesInExercise = $exercise->getQuestionWithCategories();
		// the array given by selectQuestionList start at indice 1 and not at indice 0 !!! ???
		$categories = array();
        if (!empty($categoriesInExercise)) {
			foreach ($categoriesInExercise as $category) {
				//$category['id'] = $category['iid'];
				$categories[$category['id']] = $category;
			}
		}

		return $categories;
	}

	/**
	 * @param Exercise $exercise_obj
	 * @return array
	 */
	public static function getListOfCategoriesIDForTestObject(Exercise $exercise_obj)
	{
		// parcourir les questions d'un test, recup les categories uniques dans un tableau
		$categories_in_exercise = array();
		// $question_list = $exercise_obj->getQuestionList();
		$question_list = $exercise_obj->getQuestionOrderedListByName();

		// the array given by selectQuestionList start at indice 1 and not at indice 0 !!! ???
		foreach ($question_list as $questionInfo) {
			$question_id = $questionInfo['question_id'];
			$category_list = self::getCategoryForQuestion($question_id);
			if (is_numeric($category_list)) {
				$category_list = array($category_list);
			}

			if (!empty($category_list)) {
				$categories_in_exercise = array_merge($categories_in_exercise, $category_list);
			}
		}
		if (!empty($categories_in_exercise)) {
			$categories_in_exercise = array_unique(array_filter($categories_in_exercise));
		}
		return $categories_in_exercise;
	}

	/**
	 * Return the list of differents categories NAME for a test
	 * @param int exercise id
	 * @param bool
	 * @return array of string
	 *
     * @author function rewrote by jmontoya
	 */
	public static function getListOfCategoriesNameForTest($exercise_id, $grouped_by_category = true)
    {
		$result = array();
		$categories = self::getListOfCategoriesIDForTest($exercise_id, $grouped_by_category);

		foreach ($categories as $catInfo) {
			$categoryId = $catInfo['id'];
			if (!empty($categoryId)) {
				$result[$categoryId] = array(
                    'title' => $catInfo['title'],
                    //'parent_id' =>  $catInfo['parent_id'],
					'parent_id' => '',
                    'c_id' => $catInfo['c_id']
                );
		}
		}

		return $result;
	}

	/**
	 * @param Exercise $exercise_obj
	 * @return array
	 */
	public static function getListOfCategoriesForTest(Exercise $exercise_obj)
	{
		$result = array();
		$categories = self::getListOfCategoriesIDForTestObject($exercise_obj);
		foreach ($categories as $cat_id) {
			$cat = new TestCategory($cat_id);
			$cat = (array)$cat;
			$cat['iid'] = $cat['id'];
			$cat['title'] = $cat['name'];
			$result[$cat['id']] = $cat;
		}
		return $result;
	}

	/**
	 * return the number of differents categories for a test
	 * input : test_id
	 * return : integer
	 * hubert.borderiou 07-04-2011
	 */
	public static function getNumberOfCategoriesForTest($id)
    {
		return count(TestCategory::getListOfCategoriesIDForTest($id));
	}

	/**
	 * return the number of question of a category id in a test
	 * @param int $exerciseId
     * @param int $categoryId
     *
	 * @return integer
     *
	 * @author hubert.borderiou 07-04-2011
	 */
	public static function getNumberOfQuestionsInCategoryForTest($exerciseId, $categoryId)
    {
		$nbCatResult = 0;
		$quiz = new Exercise();
		$quiz->read($exerciseId);
		$tabQuestionList = $quiz->selectQuestionList();
		// the array given by selectQuestionList start at indice 1 and not at indice 0 !!! ? ? ?
		for ($i=1; $i <= count($tabQuestionList); $i++) {
			if (TestCategory::getCategoryForQuestion($tabQuestionList[$i]) == $categoryId) {
				$nbCatResult++;
			}
		}

		return $nbCatResult;
	}

	/**
	 * return the number of question for a test using random by category
	 * input  : test_id, number of random question (min 1)
	 * hubert.borderiou 07-04-2011
	 * question without categories are not counted
	 */
	public static function getNumberOfQuestionRandomByCategory($exerciseId, $in_nbrandom)
    {
		$nbquestionresult = 0;
		$tabcatid = TestCategory::getListOfCategoriesIDForTest($exerciseId);

        foreach ($tabcatid as $category) {
            if (empty($category['id'])) {
                continue;
            }

            $nbQuestionInThisCat = TestCategory::getNumberOfQuestionsInCategoryForTest($exerciseId, $category['id']);

            if ($nbQuestionInThisCat > $in_nbrandom) {
                $nbquestionresult += $in_nbrandom;
            } else {
                $nbquestionresult += $nbQuestionInThisCat;
            }
        }

		return $nbquestionresult;
	}

	/**
	 * Return an array (id=>name)
	 * tabresult[0] = get_lang('NoCategory');
     *
     * @param int $courseId
     *
     * @return array
	 *
	 */
    public static function getCategoriesIdAndName($courseId = "")
    {
		if (empty($courseId)) {
			$courseId = api_get_course_int_id();
		}
	 	$tabcatobject = TestCategory::getCategoryListInfo("", $courseId);
	 	$tabresult = array("0"=>get_lang('NoCategorySelected'));
	 	for ($i=0; $i < count($tabcatobject); $i++) {
	 		$tabresult[$tabcatobject[$i]->id] = $tabcatobject[$i]->name;
	 	}
	 	return $tabresult;
	}

    /**
     * Returns an array of question ids for each category
     * $categories[1][30] = 10, array with category id = 1 and question_id = 10
     * A question has "n" categories
     * @param int exercise
     * @param array $check_in_question_list
     * @param array $categoriesAddedInExercise
    *
    * @param int $exerciseId
    * @return array
    */
    static function getQuestionsByCat(
        $exerciseId,
        $check_in_question_list = array(),
        $categoriesAddedInExercise = array()
    ) {
        $tableQuestion = Database::get_course_table(TABLE_QUIZ_QUESTION);
        $TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
        $TBL_QUESTION_REL_CATEGORY = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_CATEGORY);
        $categoryTable = Database::get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
        $exerciseId = intval($exerciseId);
        $courseId = api_get_course_int_id();

        $sql = "SELECT DISTINCT qrc.question_id, qrc.category_id
                FROM $TBL_QUESTION_REL_CATEGORY qrc
                INNER JOIN $TBL_EXERCICE_QUESTION eq
                ON (eq.question_id = qrc.question_id)
                INNER JOIN $categoryTable c
                ON (c.id = qrc.category_id)
                INNER JOIN $tableQuestion q
                ON (q.id = qrc.question_id )
                WHERE
                  exercice_id = $exerciseId AND
                  qrc.c_id = ".$courseId."
                ";

        $res = Database::query($sql);
        $categories = array();
        while ($data = Database::fetch_array($res)) {
            if (!empty($check_in_question_list)) {
                if (!in_array($data['question_id'], $check_in_question_list)) {
                    continue;
                }
            }

            if (!isset($categories[$data['category_id']]) ||
                !is_array($categories[$data['category_id']])
            ) {
                $categories[$data['category_id']] = array();
            }

            $categories[$data['category_id']][] = $data['question_id'];
        }

        if (!empty($categoriesAddedInExercise)) {
            $newCategoryList = array();
            foreach ($categoriesAddedInExercise as $category) {
                $categoryId = $category['category_id'];
                if (isset($categories[$categoryId])) {
                    $newCategoryList[$categoryId] = $categories[$categoryId];
                }
            }

            $checkQuestionsWithNoCategory = false;
            foreach ($categoriesAddedInExercise as $category) {
                if (empty($category['category_id'])) {
                    // Check
                    $checkQuestionsWithNoCategory = true;
                    break;
                }
            }

            // Select questions that don't have any category related
            if ($checkQuestionsWithNoCategory) {
                $originalQuestionList = $check_in_question_list;
                foreach ($originalQuestionList as $questionId) {
                    $categoriesFlatten = array_flatten($categories);
                    if (!in_array($questionId, $categoriesFlatten)) {
                        $newCategoryList[0][] = $questionId;
                    }
                }
            }

            $categories = $newCategoryList;

        }

        return $categories;
	}

	/**
	 * return a tab of $in_number random elements of $in_tab
	 */
    public static function getNElementsFromArray($in_tab, $in_number)
    {
		$tabres = $in_tab;
		shuffle($tabres);
		if ($in_number < count($tabres)) {
			$tabres = array_slice($tabres, 0, $in_number);
		}
		return $tabres;
	}

	/**
	 * display the category
	 */
	public static function displayCategoryAndTitle($questionId, $in_display_category_name = 1)
    {
        echo self::returnCategoryAndTitle($questionId, $in_display_category_name);
	}

    /**
     * @param int $questionId
     * @param int $in_display_category_name
     * @return null|string
     */
    public static function returnCategoryAndTitle($questionId, $in_display_category_name = 1)
    {
        $is_student = !(api_is_allowed_to_edit(null,true) || api_is_session_admin());
        // @todo fix $_SESSION['objExercise']
        $objExercise = isset($_SESSION['objExercise']) ? $_SESSION['objExercise'] : null;
        if (!empty($objExercise)) {
            $in_display_category_name = $objExercise->display_category_name;
        }
        $content = null;
		if (TestCategory::getCategoryNameForQuestion($questionId) != "" && ($in_display_category_name == 1 || !$is_student)) {
            $content .= '<div class="page-header">';
            $content .= '<h4>'.get_lang('Category').": ".TestCategory::getCategoryNameForQuestion($questionId).'</h4>';
            $content .= "</div>";
		}
        return $content;
	}

    /**
    * Display signs [+] and/or (>0) after question title if question has options
    * scoreAlwaysPositive and/or uncheckedMayScore
    */
    public function displayQuestionOption($in_objQuestion)
    {
		if ($in_objQuestion->type == MULTIPLE_ANSWER && $in_objQuestion->scoreAlwaysPositive) {
			echo "<span style='font-size:75%'> (>0)</span>";
		}
		if ($in_objQuestion->type == MULTIPLE_ANSWER && $in_objQuestion->uncheckedMayScore) {
			echo "<span style='font-size:75%'> [+]</span>";
		}
	}

	/**
	 * sortTabByBracketLabel ($tabCategoryQuestions)
	 * key of $tabCategoryQuestions are the category id (0 for not in a category)
	 * value is the array of question id of this category
	 * Sort question by Category
	*/
    public static function sortTabByBracketLabel($in_tab)
    {
		$tabResult = array();
		$tabCatName = array();	// tab of category name
		while (list($cat_id, $tabquestion) = each($in_tab)) {
			$catTitle = new TestCategory($cat_id);
			$tabCatName[$cat_id] = $catTitle->name;
		}
		reset($in_tab);
		// sort table by value, keeping keys as they are
		asort($tabCatName);
		// keys of $tabCatName are keys order for $in_tab
		while (list($key, $val) = each($tabCatName)) {
			$tabResult[$key] = $in_tab[$key];
		}
		return $tabResult;
	}

    /**
	 * return total score for test exe_id for all question in the category $in_cat_id for user
	 * If no question for this category, return ""
	 */
	public static function getCatScoreForExeidForUserid($in_cat_id, $in_exe_id, $in_user_id)
	{
		$tbl_track_attempt		= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
		$tbl_question_rel_category = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_CATEGORY);
		$in_cat_id = intval($in_cat_id);
		$in_exe_id = intval($in_exe_id);
		$in_user_id = intval($in_user_id);

		$query = "SELECT DISTINCT
		            marks, exe_id, user_id, ta.question_id, category_id
                  FROM $tbl_track_attempt ta , $tbl_question_rel_category qrc
                  WHERE
                    ta.question_id=qrc.question_id AND
                    qrc.category_id=$in_cat_id AND
                    exe_id=$in_exe_id AND user_id=$in_user_id";
		$res = Database::query($query);
		$totalcatscore = "";
		while ($data = Database::fetch_array($res)) {
			$totalcatscore += $data['marks'];
		}
		return $totalcatscore;
	}

	/**
     * return the number max of question in a category
     * count the number of questions in all categories, and return the max
     * @param int $exerciseId
     * @author - hubert borderiou
    */
    public static function getNumberMaxQuestionByCat($exerciseId)
    {
        $res_num_max = 0;
        // foreach question
		$tabcatid = TestCategory::getListOfCategoriesIDForTest($exerciseId);

        foreach ($tabcatid as $category) {
            if (empty($category['id'])) {
                continue;
            }

            $nbQuestionInThisCat = TestCategory::getNumberOfQuestionsInCategoryForTest($exerciseId, $category['id']);

            if ($nbQuestionInThisCat > $res_num_max) {
                $res_num_max = $nbQuestionInThisCat;
            }
        }
        return $res_num_max;
    }

    /**
     * Returns a category summary report
     * @params int exercise id
     * @params array pre filled array with the category_id, score, and weight
     * example: array(1 => array('score' => '10', 'total' => 20));
     */
    public static function get_stats_table_by_attempt($exercise_id, $category_list = array())
    {
        if (empty($category_list)) {
            return null;
        }
        $category_name_list = TestCategory::getListOfCategoriesNameForTest($exercise_id);

        $table = new HTML_Table(array('class' => 'data_table'));
        $table->setHeaderContents(0, 0, get_lang('Categories'));
        $table->setHeaderContents(0, 1, get_lang('AbsoluteScore'));
        $table->setHeaderContents(0, 2, get_lang('RelativeScore'));
        $row = 1;

        $none_category = array();
        if (isset($category_list['none'])) {
            $none_category = $category_list['none'];
            unset($category_list['none']);
        }

        $total = array();
        if (isset($category_list['total'])) {
            $total = $category_list['total'];
            unset($category_list['total']);
        }
        if (count($category_list) > 1) {
            foreach ($category_list as $category_id => $category_item) {
                $table->setCellContents($row, 0, $category_name_list[$category_id]);
                $table->setCellContents($row, 1, ExerciseLib::show_score($category_item['score'], $category_item['total'], false));
                $table->setCellContents($row, 2, ExerciseLib::show_score($category_item['score'], $category_item['total'], true, false, true));
                $row++;
            }

            if (!empty($none_category)) {
                $table->setCellContents($row, 0, get_lang('None'));
                $table->setCellContents($row, 1, ExerciseLib::show_score($none_category['score'], $none_category['total'], false));
                $table->setCellContents($row, 2, ExerciseLib::show_score($none_category['score'], $none_category['total'], true, false, true));
                $row++;
            }
            if (!empty($total)) {
                $table->setCellContents($row, 0, get_lang('Total'));
                $table->setCellContents($row, 1, ExerciseLib::show_score($total['score'], $total['total'], false));
                $table->setCellContents($row, 2, ExerciseLib::show_score($total['score'], $total['total'], true, false, true));
            }
            return $table->toHtml();
        }

        return null;
    }

    /**
	 * @return array
	 */
	function get_all_categories()
	{
		$table = Database::get_course_table(TABLE_QUIZ_CATEGORY);
		$sql = "SELECT * FROM $table ORDER BY title ASC";
		$res = Database::query($sql);
		while ($row = Database::fetch_array($res,'ASSOC')) {
			$array[] = $row;
		}
		return $array;
	}

	/**
	 * @param Exercise $exercise
	 * @param int $course_id
	 * @param string $order
	 * @param bool $shuffle
	 * @param bool $excludeCategoryWithNoQuestions
	 * @return array|bool
	 */
	public function getCategoryExerciseTree(
		$exercise,
		$course_id,
		$order = null,
		$shuffle = false,
		$excludeCategoryWithNoQuestions = true
	) {
        if (empty($exercise)) {
            return array();
        }

        if (!$exercise->specialCategoryOrders) {
            return false;
        }

        $course_id = intval($course_id);
		$table = Database::get_course_table(TABLE_QUIZ_REL_CATEGORY);
        $categoryTable = Database::get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
		$sql = "SELECT * FROM $table qc
              	LEFT JOIN $categoryTable c
                ON (qc.c_id = c.c_id AND c.id = qc.category_id)
                WHERE qc.c_id = $course_id AND exercise_id = {$exercise->id} ";

		if (!empty($order)) {
			$sql .= "ORDER BY $order";
		}

		$categories = array();

		$result = Database::query($sql);
		if (Database::num_rows($result)) {
			while ($row = Database::fetch_array($result, 'ASSOC')) {
				if ($excludeCategoryWithNoQuestions) {
					if ($row['count_questions'] == 0) {
						continue;
					}
				}
				if (empty($row['title']) && empty($row['category_id'])) {
					$row['title'] = get_lang('NoCategory');
				}
                $categories[$row['category_id']] = $row;
			}
		}

		if ($shuffle) {
			shuffle_assoc($categories);
		}

		return $categories;
	}

	public function getForm(& $form, $action = 'new')
	{
		switch($action) {
			case 'new':
				$header = get_lang('AddACategory');
				$submit = get_lang('AddTestCategory');
				break;
			case 'edit':
				$header = get_lang('EditCategory');
				$submit = get_lang('ModifyCategory');
				break;
		}

		// settting the form elements
		$form->addElement('header', $header);
		$form->addElement('hidden', 'category_id');
		$form->addElement('text', 'category_name', get_lang('CategoryName'), array('class' => 'span6'));
		$form->add_html_editor('category_description', get_lang('CategoryDescription'), false, false, array('ToolbarSet' => 'test_category', 'Width' => '90%', 'Height' => '200'));
		$category_parent_list = array();

		$options = array(
				'1' => get_lang('Visible'),
				'0' => get_lang('Hidden')
		);
		$form->addElement('select', 'visibility', get_lang('Visibility'), $options);
		$script = null;
		if (!empty($this->parent_id)) {
			$parent_cat = new TestCategory($this->parent_id);
			$category_parent_list = array($parent_cat->id => $parent_cat->name);
			$script .= '<script>$(function() { $("#parent_id").trigger("addItem",[{"title": "'.$parent_cat->name.'", "value": "'.$parent_cat->id.'"}]); });</script>';
		}
		$form->addElement('html', $script);

		$form->addElement('select', 'parent_id', get_lang('Parent'), $category_parent_list, array('id' => 'parent_id'));
		$form->addElement('style_submit_button', 'SubmitNote', $submit, 'class="add"');

		// setting the defaults
		$defaults = array();
		$defaults["category_id"] = $this->id;
		$defaults["category_name"] = $this->name;
		$defaults["category_description"] = $this->description;
		$defaults["parent_id"] = $this->parent_id;
		$defaults["visibility"] = $this->visibility;
		$form->setDefaults($defaults);

		// setting the rules
		$form->addRule('category_name', get_lang('ThisFieldIsRequired'), 'required');
	}

	/**
	 * Returns the category form.
	 * @param Exercise $exercise_obj
	 * @return string
	 */
	public function returnCategoryForm(Exercise $exercise_obj)
	{
		$categories = $this->getListOfCategoriesForTest($exercise_obj);

		$saved_categories = $exercise_obj->get_categories_in_exercise();
		$return = null;

		if (!empty($categories)) {
			$nbQuestionsTotal = $exercise_obj->getNumberQuestionExerciseCategory();
			$exercise_obj->setCategoriesGrouping(true);
			$real_question_count = count($exercise_obj->getQuestionList());

			$warning = null;
			if ($nbQuestionsTotal != $real_question_count) {
				$warning = Display::return_message(get_lang('CheckThatYouHaveEnoughQuestionsInYourCategories'), 'warning');
			}

			$return .= $warning;
			$return .= '<table class="data_table">';
			$return .= '<tr>';
			$return .= '<th height="24">' . get_lang('Categories') . '</th>';
			$return .= '<th width="70" height="24">' . get_lang('Number') . '</th></tr>';

			$emptyCategory = array(
				'id' => '0',
				'name' => get_lang('NoCategory'),
				'description' => '',
				'iid' => '0',
				'title' => get_lang('NoCategory')
			);

			$categories[] = $emptyCategory;

			foreach ($categories as $category) {
				$cat_id = $category['iid'];
				$return .= '<tr>';
				$return .= '<td>';
				//$return .= Display::div(isset($category['parent_path']) ? $category['parent_path'] : '');
				$return .= Display::div($category['name']);
				$return .= '</td>';
				$return .= '<td>';
				$value = isset($saved_categories) && isset($saved_categories[$cat_id]) ? $saved_categories[$cat_id]['count_questions'] : -1;
				$return .= '<input name="category['.$cat_id.']" value="' .$value.'" />';
				$return .= '</td>';
				$return .= '</tr>';
			}

			$return .= '</table>';
			$return .= get_lang('ZeroMeansNoQuestionWillBeSelectedMinusOneMeansThatAllQuestionsWillBeSelected');
			return $return;
		}
	}

	/**
	 * Sorts an array
	 * @param $array
	 * @return mixed
	 */
	public function sort_tree_array($array)
	{
		foreach ($array as $key => $row) {
			$parent[$key] = $row['parent_id'];
		}
		if (count($array) > 0) {
			array_multisort($parent, SORT_ASC, $array);
		}
		return $array;
	}

	/**
     * Return true if a category already exists with the same name
     * @param string $in_name
     *
     * @return bool
     */
    public static function category_exists_with_title($in_name)
    {
        $tab_test_category = TestCategory::getCategoryListInfo("title");
        foreach ($tab_test_category as $title) {
            if ($title == $in_name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the id of the test category with title = $in_title
     * @param $in_title
     * @param int $in_c_id
     *
     * @return int is id of test category
     */
    public static function get_category_id_for_title($title, $courseId = 0)
    {
        $out_res = 0;
        if (empty($courseId)) {
            $courseId = api_get_course_int_id();
        }
        $courseId = intval($courseId);
        $tbl_cat = Database::get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
        $sql = "SELECT id FROM $tbl_cat
                WHERE c_id = $courseId AND title = '".Database::escape_string($title)."'";
        $res = Database::query($sql);
        if (Database::num_rows($res) > 0) {
            $data = Database::fetch_array($res);
            $out_res = $data['id'];
        }
        return $out_res;
    }

    /**
     * Add a relation between question and category in table c_quiz_question_rel_category
     * @param int $categoryId
     * @param int $questionId
     * @param int $courseId
	 *
	 * @return int
     */
    public static function add_category_for_question_id($categoryId, $questionId, $courseId)
    {
        $table = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_CATEGORY);
        // if question doesn't have a category
        // @todo change for 1.10 when a question can have several categories
        if (TestCategory::getCategoryForQuestion($questionId, $courseId) == 0 &&
			$questionId > 0 &&
			$courseId > 0
        ) {
            $sql = "INSERT INTO $table (c_id, question_id, category_id)
                    VALUES (".intval($courseId).", ".intval($questionId).", ".intval($categoryId).")";
            Database::query($sql);
			$id = Database::insert_id();

			return $id;
        }

		return false;
    }

    /**
     * @param int $courseId
     * @param int $sessionId
     *
     * @return array
     */
    public function getCategories($courseId, $sessionId = 0)
    {
        $table = Database::get_course_table(TABLE_QUIZ_QUESTION_CATEGORY);
        $itemProperty = Database::get_course_table(TABLE_ITEM_PROPERTY);
        $sessionId = intval($sessionId);
        $courseId = intval($courseId);

        if (empty($sessionId)) {
            $sessionCondition = api_get_session_condition($sessionId, true, false, 'i.session_id');
        } else {
            $sessionCondition = api_get_session_condition($sessionId, true, true, 'i.session_id');
        }

        if (empty($courseId)) {
            return array();
        }

        $sql = "SELECT c.* FROM $table c
                INNER JOIN $itemProperty i
                ON c.c_id = i.c_id AND i.ref = c.id
                WHERE
                    c.c_id = $courseId AND
                    i.tool = '".TOOL_TEST_CATEGORY."'
                    $sessionCondition
                ORDER BY title";
        $result = Database::query($sql);

        return Database::store_result($result, 'ASSOC');
    }

    /**
     * @param int $courseId
     * @param int $sessionId
     * @return string
     */
    public function displayCategories($courseId, $sessionId = 0)
    {
        $categories = $this->getCategories($courseId, $sessionId);
        $html = '';

        foreach ($categories as $category) {
            $tmpobj = new TestCategory($category['id']);
            $nb_question = $tmpobj->getCategoryQuestionsNumber();
            $rowname = self::protectJSDialogQuote($category['title']);
            $nb_question_label = $nb_question == 1 ? $nb_question . ' ' . get_lang('Question') : $nb_question . ' ' . get_lang('Questions');

            //$html .= '<div class="sectiontitle" id="id_cat' . $category['id'] . '">';
            $content = "<span style='float:right'>" . $nb_question_label . "</span>";

            $content .= '<div class="sectioncomment">';
            $content .= $category['description'];
            $content .= '</div>';

            $links = '<a href="' . api_get_self() . '?action=editcategory&category_id=' . $category['id'] . '">' .
                Display::return_icon('edit.png', get_lang('Edit'), array(), ICON_SIZE_SMALL) . '</a>';
            $links .= ' <a href="' . api_get_self() . '?action=deletecategory&category_id=' . $category['id'] . '" ';
            $links .= 'onclick="return confirmDelete(\'' . self::protectJSDialogQuote(get_lang('DeleteCategoryAreYouSure') . '[' . $rowname) . '] ?\', \'id_cat' . $category['id'] . '\');">';
            $links .= Display::return_icon('delete.png', get_lang('Delete'), array(), ICON_SIZE_SMALL) . '</a>';
            $html .= Display::panel($content, $category['title'].$links);
        }

        return $html;
    }

    // To allowed " in javascript dialog box without bad surprises
    // replace " with two '
    public function protectJSDialogQuote($in_txt)
    {
        $res = $in_txt;
        $res = str_replace("'", "\'", $res);
        $res = str_replace('"', "\'\'", $res); // super astuce pour afficher les " dans les boite de dialogue
        return $res;
    }
}
