import chalk from 'chalk';
import path from 'path';
import {findMostCommonVersions, getAllPackages} from './utils/deps-utils.js';

function findMismatches(packages, mostCommonVersions) {
  const mismatches = [];

  packages.forEach(pkg => {
    const deps = {
      ...(pkg.dependencies || {}),
      ...(pkg.devDependencies || {}),
    };

    Object.entries(deps).forEach(([dep, version]) => {
      if (mostCommonVersions[dep] && mostCommonVersions[dep] !== version) {
        mismatches.push({
          dependency: dep,
          commonVersion: mostCommonVersions[dep],
          currentVersion: version,
          location: path.dirname(pkg.filePath),
          type: pkg.type,
        });
      }
    });
  });

  return mismatches;
}

function checkDependencies() {
  console.log(chalk.blue('🔍 Checking dependencies across the project...\n'));

  const packages = getAllPackages();

  const mostCommonVersions = findMostCommonVersions(packages);

  const mismatches = findMismatches(packages, mostCommonVersions);

  if (mismatches.length === 0) {
    console.log(chalk.green('✅ All dependencies match the most common versions!'));
    return;
  }

  console.log(chalk.yellow('⚠️ Found dependency version mismatches:\n'));

  const groupedMismatches = mismatches.reduce((acc, mismatch) => {
    if (!acc[mismatch.dependency]) {
      acc[mismatch.dependency] = [];
    }
    acc[mismatch.dependency].push(mismatch);
    return acc;
  }, {});

  Object.entries(groupedMismatches).forEach(([dep, mismatches]) => {
    console.log(chalk.cyan(`📦 ${dep}`));
    console.log(`   Most common version: ${chalk.green(mismatches[0].commonVersion)}\n`);

    mismatches.forEach(({currentVersion, location, type}) => {
      console.log(`   ${type} version: ${chalk.red(currentVersion)}`);
      console.log(`   Location: ${chalk.gray(location)}\n`);
    });
  });
}

checkDependencies();
