# Lists each "## Step" heading as [done] or [todo], from a #[done] marker above it (any case)
FNR == 1 { print "\n" FILENAME; done = 0 }
tolower($0) ~ /^#\[done\][[:space:]]*$/ { done = 1; next }
/^## Step/ { print (done ? "  [done] " : "  [todo] ") $0; done = 0; next }
NF && !/^---/ { done = 0 }
