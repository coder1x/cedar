import chalk from 'chalk';
import {
  findMostCommonVersions,
  getAllPackages,
  readPackageJson,
  writePackageJson,
} from './utils/deps-utils.js';

function updatePackageJson(filePath, mostCommonVersions) {
  const pkg = readPackageJson(filePath);
  if (!pkg) return false;

  let updated = false;
  const updateDeps = deps => {
    if (!deps) return;
    Object.keys(deps).forEach(dep => {
      if (mostCommonVersions[dep] && deps[dep] !== mostCommonVersions[dep]) {
        console.log(
          chalk.yellow(`   Updating ${dep} from ${deps[dep]} to ${mostCommonVersions[dep]}`)
        );
        deps[dep] = mostCommonVersions[dep];
        updated = true;
      }
    });
  };

  updateDeps(pkg.dependencies);
  updateDeps(pkg.devDependencies);

  if (updated) {
    return writePackageJson(filePath, pkg);
  }

  return true;
}

function fixDependencies() {
  console.log(chalk.blue('🔍 Finding and fixing dependency versions...\n'));

  const packages = getAllPackages();

  const mostCommonVersions = findMostCommonVersions(packages);

  console.log(chalk.cyan('📦 Most common versions found:'));
  Object.entries(mostCommonVersions).forEach(([dep, version]) => {
    console.log(`   ${dep}: ${chalk.green(version)}`);
  });
  console.log('');

  let success = true;
  const allFiles = packages.map(pkg => pkg.filePath);

  allFiles.forEach(file => {
    console.log(chalk.blue(`\n🔄 Updating ${file}...`));
    if (!updatePackageJson(file, mostCommonVersions)) {
      success = false;
    }
  });

  if (success) {
    console.log(
      chalk.green('\n✅ All dependencies have been updated to the most common versions!')
    );
    console.log(chalk.yellow('\n⚠️ Please run "pnpm install" to update your node_modules'));
  } else {
    console.log(chalk.red('\n❌ Some files could not be updated. Please check the errors above.'));
  }
}

fixDependencies();
