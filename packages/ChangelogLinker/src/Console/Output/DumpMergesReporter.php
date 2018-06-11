<?php declare(strict_types=1);

namespace Symplify\ChangelogLinker\Console\Output;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\ChangelogLinker\ChangeTree\Change;
use Symplify\ChangelogLinker\ChangeTree\ChangeSorter;

final class DumpMergesReporter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param Change[] $changes
     */
    public function reportChanges(array $changes): void
    {
        $this->symfonyStyle->newLine(1);

        foreach ($changes as $change) {
            $this->symfonyStyle->writeln($change->getMessage());
        }

        $this->symfonyStyle->newLine(1);
    }

    /**
     * @param Change[] $changes
     */
    public function reportChangesWithHeadlines(
        array $changes,
        bool $withCategories,
        bool $withPackages,
        string $priority
    ): void {
        // only categories
        if ($withCategories && ! $withPackages) {
            $this->reportChangesByCategories($changes);
            return;
        }

        // only packages
        if ($withPackages && ! $withCategories) {
            $this->reportChangesByPackages($changes);
            return;
        }

        $this->reportChangesByCategoriesAndPackages($changes, $priority);
    }

    /**
     * @param Change[] $changes
     */
    private function reportChangesByPackages(array $changes): void
    {
        $previousPackage = '';
        foreach ($changes as $change) {
            if ($previousPackage !== $change->getPackage()) {
                $this->symfonyStyle->newLine(1);
                $this->symfonyStyle->writeln('### ' . $change->getPackage());
                $this->symfonyStyle->newLine(1);
            }

            // @todo remove [Package] from message, maybe add getMessageWithoutPackage()
            $this->symfonyStyle->writeln($change->getMessage());

            $previousPackage = $change->getPackage();
        }

        $this->symfonyStyle->newLine(1);
        return;
    }

    /**
     * @param Change[] $changes
     */
    private function reportChangesByCategories(array $changes): void
    {
        $previousCategory = '';
        foreach ($changes as $change) {
            if ($previousCategory !== $change->getCategory()) {
                $this->symfonyStyle->newLine(1);
                $this->symfonyStyle->writeln('### ' . $change->getCategory());
                $this->symfonyStyle->newLine(1);
            }

            $this->symfonyStyle->writeln($change->getMessage());

            $previousCategory = $change->getCategory();
        }

        $this->symfonyStyle->newLine(1);
    }

    /**
     * @param Change[] $changes
     */
    private function reportChangesByCategoriesAndPackages(array $changes, string $priority): void
    {
        $previousPrimary = '';
        $previousSecondary = '';

        foreach ($changes as $change) {
            if ($priority === ChangeSorter::PRIORITY_PACKAGES) {
                $currentPrimary = $change->getPackage();
                $currentSecondary = $change->getCategory();
            } else {
                $currentPrimary = $change->getCategory();
                $currentSecondary = $change->getPackage();
            }

            $this->reportHeadline($previousPrimary, $currentPrimary, $previousSecondary, $currentSecondary);

            // @todo remove [Package] from message, maybe add getMessageWithoutPackage()
            $this->symfonyStyle->writeln($change->getMessage());

            $previousPrimary = $currentPrimary;
            $previousSecondary = $currentSecondary;
        }

        $this->symfonyStyle->newLine(1);
    }

    private function reportHeadline(
        string $previousPrimary,
        string $currentPrimary,
        string $previousSecondary,
        string $currentSecondary
    ): void {
        $spaceAlreadyAdded = false;

        if ($previousPrimary !== $currentPrimary) {
            $this->symfonyStyle->newLine(1);
            $this->symfonyStyle->writeln('### ' . $currentPrimary);
            $this->symfonyStyle->newLine(1);
            $spaceAlreadyAdded = true;

            $previousSecondary = null;
        }

        if ($previousSecondary !== $currentSecondary) {
            if (! $spaceAlreadyAdded) {
                $this->symfonyStyle->newLine(1);
            }

            $this->symfonyStyle->writeln('#### ' . $currentSecondary);
            $this->symfonyStyle->newLine(1);
        }
    }
}