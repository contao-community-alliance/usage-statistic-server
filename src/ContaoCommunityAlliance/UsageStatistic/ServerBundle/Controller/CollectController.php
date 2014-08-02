<?php

namespace ContaoCommunityAlliance\UsageStatistic\ServerBundle\Controller;

use ContaoCommunityAlliance\UsageStatistic\ServerBundle\Entity\DataName;
use ContaoCommunityAlliance\UsageStatistic\ServerBundle\Entity\DataValue;
use ContaoCommunityAlliance\UsageStatistic\ServerBundle\Entity\Installation;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="usage_statistic_server.controller.collect_controller")
 */
class CollectController extends AbstractEntityManagerAwareController
{
	/**
	 * @Route("/collect")
	 * @Method({"PUT"})
	 *
	 * @return Response
	 */
	public function collectAction(Request $request)
	{
		$content = $request->getContent();
		$body    = json_decode($content, true);

		if (!$this->validateRequestBody($body)) {
			return Response::create('Bad request', 400, ['Content-Type' => 'text/plain']);
		}

		$installationRepository = $this->entityManager->getRepository('UsageStatisticServerBundle:Installation');
		$dataNameRepository     = $this->entityManager->getRepository('UsageStatisticServerBundle:DataName');
		$dataValueRepository    = $this->entityManager->getRepository('UsageStatisticServerBundle:DataValue');
		$currentDateTime        = new \DateTime();

		// search the installation in the database...
		$installation = $installationRepository->find($body['id']);

		// ...or create a new one
		if (!$installation) {
			$installation = new Installation();
			$installation->setId($body['id']);

			$this->entityManager->persist($installation);
			$this->entityManager->flush();
		}

		// find youngest data value timestamp
		$queryBuilder = $dataValueRepository->createQueryBuilder('d');
		$queryBuilder
			->select('d.datetime')
			->innerJoin('d.installation', 'i')
			->where('i.id=:id')
			->setParameter('id', $installation->getId())
			->orderBy('d.datetime', 'DESC')
			->setMaxResults(1);
		$query = $queryBuilder->getQuery();

		try {
			$youngestDateTime = new \DateTime($query->getSingleScalarResult());

			// check if there is at least one hour between now and the last collected data value
			$dateTimeDiff = $youngestDateTime->diff($currentDateTime);
			if (
				$dateTimeDiff->invert ||
				!$dateTimeDiff->y &&
				!$dateTimeDiff->m &&
				!$dateTimeDiff->d &&
				!$dateTimeDiff->h
			) {
				return Response::create('Conflict', 409, ['Content-Type' => 'text/plain']);
			}
		}
		catch (NoResultException $e) {
			// silently ignore these errors
		}

		// store new data
		if (count($body['data'])) {
			foreach ($body['data'] as $name => $value) {
				$dataName = $dataNameRepository->find($name);

				if (!$dataName) {
					$dataName = new DataName();
					$dataName->setName($name);
					$this->entityManager->persist($dataName);
				}

				$dataValue = new DataValue();
				$dataValue->setInstallation($installation);
				$dataValue->setName($dataName);
				$dataValue->setValue($value);
				$dataValue->setDateTime($currentDateTime);
				$this->entityManager->persist($dataValue);
			}

			$this->entityManager->flush();
		}

		return Response::create('Created', 201, ['Content-Type' => 'text/plain']);
	}

	/**
	 * Determine if the request body is valid.
	 *
	 * @param array $body
	 *
	 * @return bool
	 */
	protected function validateRequestBody($body)
	{
		return is_array($body) &&
		isset($body['id']) &&
		preg_match('~^[0-9a-f]{128}$~', $body['id']) &&
		isset($body['data']) &&
		$this->validateDataSet($body['data']);
	}

	/**
	 * Determine if the data set is valid.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	protected function validateDataSet($data)
	{
		if (!is_array($data)) {
			return false;
		}

		foreach ($data as $key => $value) {
			if (!is_string($key) || is_numeric($key) || !is_scalar($value)) {
				return false;
			}
		}

		return true;
	}
}
