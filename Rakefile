# -*- ruby -*-

text_domain = "mroonga"
translated_languages = [
  "ja",
]
mo_files = translated_languages.collect do |language|
  "languages/#{text_domain}-#{language}.mo"
end

php_files = Dir.glob("*.php")

def detect_version
  mroonga_php = File.join(__dir__, "mroonga.php")
  File.open(mroonga_php) do |input|
    input.each_line do |line|
      case line.chomp
      when /\AVersion: (.+)\z/
        return $1
      end
    end
  end
  nil
end

version = ENV["VERSION"] || detect_version



pot_file = "languages/#{text_domain}.pot"

file pot_file => php_files do
  sh("xgettext",
     "--language", "php",
     "--keyword", "__",
     "--output", pot_file,
     "--package-name", "Mroonga",
     "--package-version", version,
     "--msgid-bugs-address",
     "https://github.com/mroonga/wordpress-mroonga/issues/new",
     *php_files)
end

translated_languages.each do |language|
  po_file = "languages/#{text_domain}-#{language}.po"
  file po_file => pot_file do
    if File.exist?(po_file)
      sh("msgmerge",
         "--output-file", po_file,
         po_file,
         pot_file)
    else
      sh("msginit",
         "--input", pot_file,
         "--output-file", po_file,
         "--locale", language,
         "--no-translator")
    end
  end
end

rule ".mo" => ".po" do |task|
  sh("msgfmt",
     "--output-file", task.name,
     task.source)
end

desc "Update translation"
task :translate => mo_files
