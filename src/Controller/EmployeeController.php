<?php

namespace App\Controller;

use App\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class EmployeeController extends AbstractController
{
    #[Route('/api/employees', name: 'api_employees', methods: ['GET'])]
    public function list(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $name = $request->query->get('name');
        $email = $request->query->get('email');

        $queryBuilder = $em->getRepository(Employee::class)->createQueryBuilder('e');

        if ($name) {
            $queryBuilder->where('e.firstName LIKE :name OR e.lastName LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($email) {
            $queryBuilder->andWhere('e.email = :email')
                ->setParameter('email', $email);
        }

        $employees = $queryBuilder->getQuery()->getArrayResult();

        return new JsonResponse($employees);
    }

    #[Route('/api/employees', name: 'api_employees_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar que no haya duplicados en el campo email
        $existingEmployee = $em->getRepository(Employee::class)->findOneBy(['email' => $data['email']]);
        if ($existingEmployee) {
            return new JsonResponse(['error' => 'Email already exists'], 400);
        }

        $employee = new Employee();
        $employee->setFirstName($data['firstName']);
        $employee->setLastName($data['lastName']);
        $employee->setPosition($data['position']);
        $employee->setBirthDate(new \DateTime($data['birthDate']));
        $employee->setEmail($data['email']);

        $em->persist($employee);
        $em->flush();

        return new JsonResponse(['message' => 'Employee created successfully'], 201);
    }

    #[Route('/api/employees/{id}', name: 'api_employees_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $employee = $em->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return new JsonResponse(['error' => 'Employee not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['firstName'])) {
            $employee->setFirstName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $employee->setLastName($data['lastName']);
        }

        if (isset($data['email'])) {
            // Validar que no se duplique el email
            $existingEmployee = $em->getRepository(Employee::class)->findOneBy(['email' => $data['email']]);
            if ($existingEmployee && $existingEmployee->getId() !== $employee->getId()) {
                return new JsonResponse(['error' => 'Email already exists'], 400);
            }
            $employee->setEmail($data['email']);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Employee updated successfully']);
    }

    #[Route('/api/employees/{id}', name: 'api_employees_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $employee = $em->getRepository(Employee::class)->find($id);

        if (!$employee) {
            return new JsonResponse(['error' => 'Employee not found'], 404);
        }

        $em->remove($employee);
        $em->flush();

        return new JsonResponse(['message' => 'Employee deleted successfully']);
    }
}
